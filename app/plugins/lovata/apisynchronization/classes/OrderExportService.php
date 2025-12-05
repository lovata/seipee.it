<?php namespace Lovata\ApiSynchronization\classes;

use Lovata\OrdersShopaholic\Models\Order;
use Lovata\OrdersShopaholic\Models\OrderPosition;
use Log;

/**
 * Service for exporting orders to Seipee API
 */
class OrderExportService
{
    /** @var ApiClientService */
    protected $apiClient;

    /** @var Order */
    protected $order;

    public function __construct()
    {
        $this->apiClient = new ApiClientService();
    }

    /**
     * Export order to Seipee API
     *
     * @param Order $order
     * @return bool
     */
    public function exportOrder(Order $order): bool
    {
        $this->order = $order;

        try {
            // Authenticate
            $this->apiClient->authenticate();

            // Step 1: Create order header
            $headerId = $this->createOrderHeader();

            if (!$headerId) {
                Log::error('Seipee Order Export: Failed to create order header', [
                    'order_id' => $order->id,
                ]);
                return false;
            }

            // Step 2: Create order lines
            $this->createOrderLines($headerId);

            // Step 3: Save Seipee order ID to database
            $order->seipee_order_id = $headerId;
            $order->save();

            Log::info('Seipee Order Export: Order exported successfully', [
                'order_id' => $order->id,
                'seipee_header_id' => $headerId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Seipee Order Export: Exception occurred', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Create order header document
     *
     * @return int|null Header document ID
     * @throws \RuntimeException
     */
    protected function createOrderHeader(): ?int
    {
        $order = $this->order;

        // Get customer code from order properties
        $customerCode = $this->getCustomerCode();

        if (!$customerCode) {
            throw new \RuntimeException('Customer code (Cd_CF) not found in order properties');
        }

        // Prepare header data
        $jsonRow = [
            'Cd_CF' => $customerCode,
            'DataDoc' => $order->created_at->format('Y-m-d\TH:i:s'),
        ];

        // Add optional fields if present in order properties
        if ($destination = $this->getOrderProperty('Cd_CFDest')) {
            $jsonRow['Cd_CFDest'] = $destination;
        }

        if ($deliveryDate = $this->getOrderProperty('DataConsegna')) {
            $jsonRow['DataConsegna'] = $deliveryDate;
        }

        if ($carrier1 = $this->getOrderProperty('Cd_DoVettore_1')) {
            $jsonRow['Cd_DoVettore_1'] = $carrier1;
        }

        if ($carrier2 = $this->getOrderProperty('Cd_DoVettore_2')) {
            $jsonRow['Cd_DoVettore_2'] = $carrier2;
        }

        if ($port = $this->getOrderProperty('Cd_DOPorto')) {
            $jsonRow['Cd_DOPorto'] = $port;
        }

        if ($agent = $this->getOrderProperty('Cd_Agente')) {
            $jsonRow['Cd_Agente'] = $agent;
        }

        if ($agent2 = $this->getOrderProperty('Cd_Agente_2')) {
            $jsonRow['Cd_Agente_2'] = $agent2;
        }

        if ($plan = $this->getOrderProperty('Cd_PG')) {
            $jsonRow['Cd_PG'] = $plan;
        }

        // Create header
        $response = $this->apiClient->post(
            'xbt_seipee_b2b_DOTes',
            'Id_xbt_seipee_b2b_DOTes = 0',
            $jsonRow,
            0,
            0
        );

        // Extract header ID from response
        if (isset($response['result'][0]['Id_xbt_seipee_b2b_DOTes'])) {
            return (int) $response['result'][0]['Id_xbt_seipee_b2b_DOTes'];
        }

        return null;
    }

    /**
     * Create order lines for the header
     *
     * @param int $headerId
     * @throws \RuntimeException
     */
    protected function createOrderLines(int $headerId): void
    {
        $order = $this->order;

        foreach ($order->order_position as $position) {
            $this->createOrderLine($headerId, $position);
        }
    }

    /**
     * Create single order line
     *
     * @param int $headerId
     * @param OrderPosition $position
     * @throws \RuntimeException
     */
    protected function createOrderLine(int $headerId, OrderPosition $position): void
    {
        // Get product code from offer
        $productCode = $this->getProductCode($position);

        if (!$productCode) {
            Log::warning('Seipee Order Export: Product code not found for order position', [
                'order_id' => $this->order->id,
                'position_id' => $position->id,
            ]);
            return;
        }

        // Prepare line data
        $jsonRow = [
            'Id_xbt_seipee_b2b_DOTes' => $headerId,
            'Cd_AR' => $productCode,
            'Qta' => (float) $position->quantity,
            'PrezzoUnitarioV' => (float) $position->price_value,
        ];

        // Add optional fields
        if ($position->product && $position->product->name) {
            $jsonRow['Descrizione'] = mb_substr($position->product->name, 0, 80);
        }

        // Add delivery date if present
        if ($deliveryDate = $this->getPositionProperty($position, 'DataConsegna')) {
            $jsonRow['DataConsegna'] = $deliveryDate;
        }

        // Add line notes if present
        if ($position->property && isset($position->property['NoteRiga'])) {
            $jsonRow['NoteRiga'] = mb_substr($position->property['NoteRiga'], 0, 240);
        }

        // Create line
        $response = $this->apiClient->post(
            'xbt_seipee_b2b_DORig',
            'Id_xbt_seipee_b2b_DORig = 0',
            $jsonRow,
            0,
            0
        );

        Log::debug('Seipee Order Export: Order line created', [
            'order_id' => $this->order->id,
            'position_id' => $position->id,
            'product_code' => $productCode,
        ]);
    }

    /**
     * Get customer code from order properties
     *
     * @return string|null
     */
    protected function getCustomerCode(): ?string
    {
        $order = $this->order;

        // Try to get from order properties
        if ($code = $this->getOrderProperty('Cd_CF')) {
            return $code;
        }

        // Try to get from user properties if available
        if ($order->user && isset($order->user->property['Cd_CF'])) {
            return $order->user->property['Cd_CF'];
        }

        return null;
    }

    /**
     * Get product code from order position
     *
     * @param OrderPosition $position
     * @return string|null
     */
    protected function getProductCode(OrderPosition $position): ?string
    {
        // Try to get from offer properties
        if ($position->offer && isset($position->offer->property['seipee_code'])) {
            return $position->offer->property['seipee_code'];
        }

        // Try to get from product properties
        if ($position->product && isset($position->product->property['seipee_code'])) {
            return $position->product->property['seipee_code'];
        }

        // Fallback to offer code or product code
        if ($position->offer && $position->offer->code) {
            return $position->offer->code;
        }

        if ($position->product && $position->product->code) {
            return $position->product->code;
        }

        return null;
    }

    /**
     * Get property value from order
     *
     * @param string $key
     * @return mixed|null
     */
    protected function getOrderProperty(string $key)
    {
        if ($this->order->property && isset($this->order->property[$key])) {
            return $this->order->property[$key];
        }

        return null;
    }

    /**
     * Get property value from order position
     *
     * @param OrderPosition $position
     * @param string $key
     * @return mixed|null
     */
    protected function getPositionProperty(OrderPosition $position, string $key)
    {
        if ($position->property && isset($position->property[$key])) {
            return $position->property[$key];
        }

        return null;
    }

    /**
     * Fetch order data from Seipee API by order ID
     * ***Don't have any access yet***
     *
     * @param int $seipeeOrderId
     * @return array|null
     * @throws \RuntimeException
     */
    public function fetchOrderFromSeipee(int $seipeeOrderId): ?array
    {
        try {
            // Authenticate if not already
            if (!$this->apiClient) {
                $this->apiClient = new ApiClientService();
            }
            $this->apiClient->authenticate();

            // Fetch order header
            $headerData = $this->apiClient->fetch(
                'xbt_seipee_b2b_DOTes',
                1,
                1,
                "Id_xbt_seipee_b2b_DOTes = {$seipeeOrderId}"
            );

            if (empty($headerData['result'])) {
                Log::warning('Seipee Order Fetch: Order not found in Seipee', [
                    'seipee_order_id' => $seipeeOrderId,
                ]);
                return null;
            }

            // Fetch order lines
            $linesData = $this->apiClient->fetch(
                'xbt_seipee_b2b_DORig',
                1,
                100,
                "Id_xbt_seipee_b2b_DOTes = {$seipeeOrderId}"
            );

            return [
                'header' => $headerData['result'][0] ?? null,
                'lines' => $linesData['result'] ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('Seipee Order Fetch: Exception occurred', [
                'seipee_order_id' => $seipeeOrderId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Sync order updates from Seipee back to local order
     *
     * @param Order $order
     * @return bool
     */
    public function syncOrderUpdatesFromSeipee(Order $order): bool
    {
        if (!$order->seipee_order_id) {
            Log::warning('Seipee Order Sync: Order has no seipee_order_id', [
                'order_id' => $order->id,
            ]);
            return false;
        }

        $seipeeData = $this->fetchOrderFromSeipee($order->seipee_order_id);

        if (!$seipeeData) {
            return false;
        }

        // Log the fetched data for debugging
        Log::info('Seipee Order Sync: Fetched order data from Seipee', [
            'order_id' => $order->id,
            'seipee_order_id' => $order->seipee_order_id,
            'header' => $seipeeData['header'],
            'lines_count' => count($seipeeData['lines']),
        ]);

        // Here you can add logic to update local order based on Seipee data
        // For example: update status, shipping info, etc.
        // This depends on your business requirements

        return true;
    }
}
