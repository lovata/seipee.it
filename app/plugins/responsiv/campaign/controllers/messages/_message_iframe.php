<?php
    $message = $formModel;
?>
<?php if ($message): ?>

    <iframe id="<?= $this->getId('messageIframe') ?>" style="width:100%;height:500px;padding:0" frameborder="0"></iframe>

    <script type="text/template" id="<?= $this->getId('messageContents') ?>">
        <?= $message->renderForPreview() ?>
    </script>
    <script>
        (function($){
            var messageContents,
                messageFrame = $('#<?= $this->getId('messageIframe') ?>')

            $(document).render(function(){
                var frameContents = messageFrame.contents().find('html')
                messageContents = $('#<?= $this->getId('messageContents') ?>').html()
                frameContents.html(messageContents)
                messageFrame.height(frameContents.height())
            })
        })(window.jQuery);
    </script>

<?php else: ?>
    <p class="flash-message static error"><?= e(__('Message template not found')) ?></p>
<?php endif ?>
