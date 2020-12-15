Matrix plugin for Kanboard
==============================

Receive Kanboard notifications on Matrix.

Author
------
- Chris Metz
- Frederic Guillot (original Mattermost plugin)
- License MIT

Requirements
------------

- Kanboard >= 1.0.37
- Matrix server

Installation
------------

You have the choice between 3 methods:

1. Install the plugin from the Kanboard plugin manager in one click
2. Download the zip file and decompress everything under the directory `plugins/Matrix`
3. Clone this repository into the folder `plugins/Matrix`

Note: Plugin folder is case-sensitive.

Configuration
-------------

Firstly, you have to generate a new webhook url in Matrix (**Integration Settings > Incoming Webhooks**).

### Receive project notifications to a room

- Go to the project settings then choose **Integrations > Matrix**
- Copy and paste the webhook url from Matrix or leave it blank if you want to use the global webhook url
- Enable Matrix in your project notifications **Notifications > Matrix**

You can also define the webhook URL globally in the **Application settings > Integrations > Matrix**.

### Matrix configuration

- Change the config option `EnablePostUsernameOverride` to `true` to have Kanboard as username
- Change `EnablePostIconOverride` to `true` to see Kanboard icon

## Troubleshooting

- Enable the debug mode
- All connection errors with the Matrix API are recorded in the log files `data/debug.log`
