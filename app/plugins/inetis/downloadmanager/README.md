# About

OctoberCMS plugin to securely share files on the frontend.

Files are stored in hierarchical folders and each folder can have a different method of protection.

**Available Protection Methods**:
 - **Public**: All visitors can access the folder
 - **Password**: A password is required to access the folder
 - **RainLab.User Groups**: If [RainLab.User](https://octobercms.com/plugin/rainlab-user) is installed, a front-end user group can be specified that will then require the user to be logged in and part of that group in order to gain access to the folder
 - **Inherit**: Protection method configuration is inherited from the parent folder

## Backend Interface
![](https://user-images.githubusercontent.com/12028540/35440777-2d45652c-02a1-11e8-9a8c-5f6c4c3f38a7.gif)

## Front-end
### DownloadManagerBrowser Component
Displays the selected folder's contents (both files and sub-folders) recursively.

[See the demo website](https://demo.inetis.ch/plugins/download-manager)

![](https://user-images.githubusercontent.com/12028540/33614640-3adce10c-d9d8-11e7-8440-45498ad804e2.png)

#### Configuration

The following is an example of a CMS page (`/files`) that displays all the files on the system. To change the root directory that gets displayed, set the `rootFolder` property to the folder ID that you would like to display.
```
title = "Files"
url = "/files/:path?*"
layout = "default"

[downloadManagerBrowser]
rootFolder = 0
path = "{{ :path }}"
displayBreadcrumb = true
==
<div class="container">
    {% component 'downloadManagerBrowser' %}
</div>
```

> **NOTE:** In order for recursive folders to work correctly, the `path` property needs to be set to a URL parameter ending with `?*` (i.e. `/files/:path?*`) in the CMS page URL

#### Options

| Name              | Default | Description                                                                                                 |
|:------------------|:-------:|:------------------------------------------------------------------------------------------------------------|
| rootFolder        |  null   | The top level folder that will be displayed first on the page                                               |
| path              |  null   | Path of the current folder to display <br>(relative to the root folder)                                     |
| displaySubFolders |  true   | Include sub folders in the component output. Set to `false` to display only the files of the current folder |
| displayTitle      |  true   | Show the name of the current category as a "h2" level heading above the description                         |
| displayBreadcrumb |  false  | Show a breadcrumb on top of the folders list. Set to `true` to enable this feature                          |

### DownloadManagerPasswordForm component
Displays a form where visitors can enter folder passwords. When a password is entered, access to the folder will be temporarily granted and the visitor will be redirected to the folder.
![](https://user-images.githubusercontent.com/12028540/35442277-f69feece-02a6-11e8-968f-05e5c689038d.gif)

#### Configuration
```
title = "Password form"
url = "/files-password"
layout = "default"

[downloadManagerPasswordForm]
page = "files.htm"
==
<div class="container">
    {% component 'downloadManagerPasswordForm' %}
</div>
```

The `page` property must be set to a CMS page that contains a `DownloadManagerBrowser` component which has its `rootFolder` property set to root (0), or a parent of the folders being accessed via the provided Password. The Browser component will prevent displaying folders that are not children of the selected `rootFolder`.

For example, in the following folder structure:
```
Documents
├───Images
├───Movies
├───├───Holidays
├───Files
Downloads
├───Builds
├───Temp
```
If `Documents` is selected as the `rootFolder`, and the submitted Password is for the `Builds` folder, `Builds` will not be available because it is not a sub folder of `Documents`. However, if the submitted Password is for the `Images` or `Holidays` folders that would work because they are both descendants of `Documents`.

#### Options
 - **page** The CMS Page the plugin will redirect visitors with a valid password to
> **NOTE:** This page must display the root folder or a parent of the folders that you are intending to share
