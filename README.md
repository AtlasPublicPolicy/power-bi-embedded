# power-bi-embedded

![Atlas](http://atlaspolicy.com/wp-content/uploads/2017/03/logo.png)

 **Title:** Power BI Embedded for WordPress

 **Contributors:** Nick Nigro [nick.nigro@atlaspolicy.com], aswingiri [aswingiri@upnrunn.com], bankerrajendra [bankerrajendra@upnrunn.com], bhoot[mithunbiswas@upnrunn.com]

 **Homepage:** [http://www.atlaspolicy.com](http://www.atlaspolicy.com)

 **Tags:** powerbi-embedded, wordpress-plugin, powerbi, wordpress

 **Tested up to:** 4.9.4

 **Stable tag:** 1.0.0

 **License:** GNU General Public License v3.0

 **License URI:** [https://www.gnu.org/licenses/lgpl.html](https://www.gnu.org/licenses/lgpl.html)

## Description
This WordPress plugin supports Microsoft Power BI Embedded, including  dashboards, reports, report visuals, Q&A, and tiles. Power BI is a sophisticated data analytics software and service package from Microsoft. More information on Power BI is available at www.powerbi.com.

**You can download this plugin on WordPress at [https://wordpress.org/plugins/embed-power-bi/](https://wordpress.org/plugins/embed-power-bi).**

This plugin uses the Power BI REST API to access various types of content and easily embed them on a WordPress site using a shortcode. The plugin follows the "app owns data" process as documented by Microsoft at [https://docs.microsoft.com/en-us/power-bi/developer/embedding-content](https://docs.microsoft.com/en-us/power-bi/developer/embedding-content). See [https://docs.microsoft.com/en-us/power-bi/developer/embedding](https://docs.microsoft.com/en-us/power-bi/developer/embedding) for more information from Microsoft on how to Power BI embed content.

The plugin relies on Microsoft's JavaScript library for embedding Power BI available on Github here: [https://github.com/Microsoft/PowerBI-JavaScript](https://github.com/Microsoft/PowerBI-JavaScript).

In order to use this plugin, users must first register an app with Azure AD to embed Power BI content. See [https://docs.microsoft.com/en-us/power-bi/developer/register-app](https://docs.microsoft.com/en-us/power-bi/developer/register-app) for more information on how to register an app. Make sure to write down your Client ID and Client Secret when you register the app.

## Plugin Settings Page
The "app owns data" process requires that a single, master Power BI Pro account controls access. The credentials for this account are configured one time on the plugin settings page. The plugin will authenticate against Azure AD with those stored credentials.

 * User name: Email address for the Power BI Pro master account.
 * Password: Password for the Power BI Pro master account.
 * Client ID: The Client ID for the app you registered with Azure AD.
 * Client Secret: The Client Secret for the app you registered with Azure AD.

After you save your changes, you'll see the authentication status on the settings page if it worked. Once you've configured the plugin, you can now add Power BI content to your WordPress site and embed it wherever you want.

## Power BI Content
The plugin uses a custom content type for each Power BI component to embed (dashboard, report, etc.). Go to "All Power BI Items" to add a new component.

 * Embed Type: Report, Report Visual, Q&A, Dashboard, Tile.

The Embed Type determines the remaining fields to fill out.

### Report

 * Report Mode: View, Edit, Create
 * Report ID: Enter the unique identifier for the report. You can find the identifier by viewing a report in the Power BI Service. The identifier is in the URL.
 * Group ID: Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.
 * Dataset ID: Enter the unique identifier for the dataset. You can find the identifier by viewing a dashboard in the Power BI Service. The identifier is in the URL. This is only needed for Create Mode.

### Report Visual

 * Report ID: Enter the unique identifier for the report. You can find the identifier by viewing a report in the Power BI Service. The identifier is in the URL.
 * Group ID: Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.
 * Page Name: Enter the unique identifier for the Page. You can find the identifier by entering viewing the page within a report in the Power BI Service. The identifier is in the URL.
 * Visual Name: The Visual Name can be retrieved using the GetVisuals method on the Page object.

### Q&A

 * Q&A Mode: Show Q&A, Show Q&A with predefined question, Show answer only with predefined question
 * Q&A Input Question: Only necessary for "Show Q&A with predefined question" and "Show answer only with predefined question"
 * Group ID: Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.
 * Dataset ID: Enter the unique identifier for the dataset. You can find the identifier by viewing a dashboard in the Power BI Service. The identifier is in the URL. This is only needed for Create Mode.

### Dashboard

 * Dashboard ID: Enter the unique identifier for the dashboard. You can find the identifier by viewing a dashboard in the Power BI Service. The identifier is in the URL.
 * Group ID: Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.

### Tile

 * Dashboard ID: Enter the unique identifier for the dashboard. You can find the identifier by viewing a dashboard in the Power BI Service. The identifier is in the URL.
 * Group ID: Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.
 * Tile ID: Enter the unique identifier for the dashboard tile. You can find the identifier by entering the focus mode for a tile when viewing a dashboard in the Power BI Service. The identifier is in the URL.


### Other Settings for Power BI Content
You can also configure how the content is embedded including whether to show the filter pane or page navigation along with the language (defines the language Power BI uses for localization and locale format (defines the text formatting that powerBI uses for dates, currency, etc.). Finally, you can set the default width and height for the container in pixels or as a percentage.

## Embedding Content in WordPress
Once the Power BI content is created in WordPress, you can embed it anywhere with a shortcode. The shortcode accepts the unique identifier for the Power BI content (visible from "All Power BI Items") and an optional width and height to customize the size of the content where it is being embedded, if you want to override the default width and/or height for the content.

[powerbi id=X width=X height=X]

## Check Resource State
You can insert shortcode into pages/posts to display content based on resource state. Check the following examples.

Display the content when resource is active.
    [powerbi_resource]
        Resource is active.
    [powerbi_resource]

Display the content when resource is paused.
    [powerbi_resource state="Paused"]
        Resource is paused.
    [powerbi_resource]

Learn more about states. [https://docs.microsoft.com/en-us/rest/api/power-bi-embedded/capacities/getdetails#state](https://docs.microsoft.com/en-us/rest/api/power-bi-embedded/capacities/getdetails#state)

## Plugin Dependencies

powerbi-client v2.4.6 [https://github.com/Microsoft/PowerBI-JavaScript](https://github.com/Microsoft/PowerBI-JavaScript), CMB2 v2.3.0 [https://github.com/CMB2/CMB2](https://github.com/CMB2/CMB2), CMB2 Conditionals v1.0.4 [(https://github.com/jcchavezs/cmb2-conditionals)](https://github.com/jcchavezs/cmb2-conditionals)
