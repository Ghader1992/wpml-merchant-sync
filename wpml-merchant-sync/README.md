# WPML Merchant Sync

Connects WooCommerce to Google Merchant Center via the Content API, fully supporting English and Arabic translations via WPML.

## Installation

1.  Download the plugin as a ZIP file.
2.  In your WordPress admin, go to **Plugins > Add New > Upload Plugin**.
3.  Choose the ZIP file you downloaded and click **Install Now**.
4.  Activate the plugin.

## Setup

1.  Go to **WooCommerce > Settings > Merchant Sync**.
2.  Enter your Google Service Account JSON.
3.  Configure the other settings as needed.

## Usage

The plugin will automatically sync your products with the Google Merchant Center. You can also manually trigger a sync from the settings page.

## REST API

The plugin exposes a REST API endpoint to get the product feed.

**Endpoint:** `/wp-json/merchant/v1/{lang}`

**Method:** `GET`

**Parameters:**

*   `lang`: `en` or `ar`
*   `format`: `json` or `xml` (optional, defaults to `json`)
*   `category`: A category slug (optional)
*   `modified_after`: A date in `Y-m-d H:i:s` format (optional)
*   `limit`: A number (optional)

**Example:**

```
curl -X GET "https://example.com/wp-json/merchant/v1/en?format=xml"
```
