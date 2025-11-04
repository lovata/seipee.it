import { defineConfig } from 'vite';
import tailwindcss from 'tailwindcss';
import { join } from 'path';
import { globSync } from 'glob';

const input = {}

const cssFolderPath = join(__dirname, 'css');
const jsFolderPath = join(__dirname, 'js');
const assetCssFileList= globSync([`${cssFolderPath}/app.css`]);
const assetJSFileList= globSync([`${jsFolderPath}/vendor/app.js`, `${jsFolderPath}/pages/*.+(js)`, `${jsFolderPath}/pages/**/*.+(js)`]);

const fileList = [...assetCssFileList, ...assetJSFileList];
fileList.forEach((filePath) => {
    const fileName = filePath.replace(join(__dirname, '/'), '');
    input[fileName] = filePath;
});

const themeName = __dirname.slice(__dirname.lastIndexOf('/') + 1);

export default defineConfig({
    base: `/themes/${themeName}`,
    plugins: [
        tailwindcss(),
    ],
    build: {
        rollupOptions: { input },
        manifest: true,
        emptyOutDir: false,
        outDir: '',
        assetsDir: 'dist',
    },
    server: {
        hmr: {
            protocol: 'ws',
        },
    }
})
