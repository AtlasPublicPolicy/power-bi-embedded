=== Power BI Embedded for WordPress ===
Contributors: atlaspolicy, upnrunn, stegel
Tags: powerbi-embedded, wordpress-plugin, powerbi, wordpress
Requires at least: 4.4.0
Requires PHP: 5.2.4
Tested up to: 5.3.2
Stable tag: 1.1.3
License: GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/lgpl.html

This WordPress plugin supports Microsoft Power BI Embedded, including dashboards, reports, report visuals, Q&A, and tiles. It also supports filters and slicers passed in as a JSON object.

== Description ==

This WordPress plugin supports Microsoft Power BI Embedded, including  dashboards, reports, report visuals, Q&A, and tiles. Power BI is a sophisticated data analytics software and service package from Microsoft. More information on Power BI is available at [www.powerbi.com](http://www.powerbi.com).

Contribute to this plugin on GitHub at [https://github.com/atlaspolicy/power-bi-embedded](https://github.com/atlaspolicy/power-bi-embedded)!

This plugin uses the Power BI REST API to access various types of content and easily embed them on a WordPress site using a shortcode. The plugin follows the "app owns data" process as documented by Microsoft at [https://docs.microsoft.com/en-us/power-bi/developer/embedding-content](https://docs.microsoft.com/en-us/power-bi/developer/embedding-content). See [https://docs.microsoft.com/en-us/power-bi/developer/embedding](https://docs.microsoft.com/en-us/power-bi/developer/embedding) for more information from Microsoft on how to Power BI embed content.

The plugin relies on Microsoft's JavaScript library for embedding Power BI available on Github here: [https://github.com/Microsoft/PowerBI-JavaScript](https://github.com/Microsoft/PowerBI-JavaScript).

In order to use this plugin, users must first register an app with Azure AD to embed Power BI content. See [https://docs.microsoft.com/en-us/power-bi/developer/register-app](https://docs.microsoft.com/en-us/power-bi/developer/register-app) for more information on how to register an app. Make sure to write down your Client ID and Client Secret when you register the app.

= Plugin Settings Page =
The "app owns data" process requires that a single, master Power BI Pro account controls access. The credentials for this account are configured one time on the plugin settings page. The plugin will authenticate against Azure AD with those stored credentials.

 * User name: Email address for the Power BI Pro master account.
 * Password: Password for the Power BI Pro master account.
 * Client ID: The Client ID for the app you registered with Azure AD.
 * Client Secret: The Client Secret for the app you registered with Azure AD.

After you save your changes, you'll see the authentication status on the settings page if it worked. Once you've configured the plugin, you can now add Power BI content to your WordPress site and embed it wherever you want.

The plugin also includes support to control the Power BI Embedded resource on Azure. This can be really helpful if you're trying to control costs by disabling the resource when it's not in use. The scheduling can be controlled on a daily basis by setting the hour to enable and the hour to disable the resource. If you don't need to disable the resource, then you can ignore this section. 

 * Tenant ID or Directory ID under Azure Active Directory for Office 365: The Directory ID is under the Properties section of the Azure Active Directory on the Azure portal. 
 * Subscription ID for Power BI Resource: Read directly from Azure portal.
 * Resource Group Name: Read directly from the Azure portal.
 * Resource Name: Read directly from the Azure portal.
 * Sunday/Saturday: Set the time to start and pause the resource on a daily basis.

 For scheduling, it is recommended to use cron on your web server instead of WP Cron. Many websites exist to walk you through the process of using your web server instead of WP Cron. For example, see [https://www.nextscripts.com/tutorials/wp-cron-scheduling-tasks-in-wordpress](https://www.nextscripts.com/tutorials/wp-cron-scheduling-tasks-in-wordpress).

= Power BI Content =
The plugin uses a custom content type for each Power BI component to embed (dashboard, report, etc.). Go to "All Power BI Items" to add a new component.

 * Embed Type: Report, Report Visual, Q&A, Dashboard, Tile.

The Embed Type determines the remaining fields to fill out.

= Report =

 * Report Mode: View, Edit, Create
 * Report ID: Enter the unique identifier for the report. You can find the identifier by viewing a report in the Power BI Service. The identifier is in the URL.
 * Group ID: Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.
 * Dataset ID: Enter the unique identifier for the dataset. You can find the identifier by viewing a dashboard in the Power BI Service. The identifier is in the URL. This is only needed for Create Mode.

= Report Visual =

 * Report ID: Enter the unique identifier for the report. You can find the identifier by viewing a report in the Power BI Service. The identifier is in the URL.
 * Group ID: Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.
 * Page Name: Enter the unique identifier for the Page. You can find the identifier by viewing the page within a report in the Power BI Service. The identifier is in the URL.
 * Visual Name: The Visual Name can be retrieved using the GetVisuals method on the Page object.

= Q&A =

 * Q&A Mode: Show Q&A, Show Q&A with predefined question, Show answer only with predefined question
 * Q&A Input Question: Only necessary for "Show Q&A with predefined question" and "Show answer only with predefined question"
 * Group ID: Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.
 * Dataset ID: Enter the unique identifier for the dataset. You can find the identifier by viewing a dashboard in the Power BI Service. The identifier is in the URL. This is only needed for Create Mode.

= Dashboard =

 * Dashboard ID: Enter the unique identifier for the dashboard. You can find the identifier by viewing a dashboard in the Power BI Service. The identifier is in the URL.
 * Group ID: Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.

= Tile =

 * Dashboard ID: Enter the unique identifier for the dashboard. You can find the identifier by viewing a dashboard in the Power BI Service. The identifier is in the URL.
 * Group ID: Enter the unique identifier for the group. You can find the identifier by viewing a dashboard or report in the Power BI Service. The identifier is in the URL.
 * Tile ID: Enter the unique identifier for the dashboard tile. You can find the identifier by entering the focus mode for a tile when viewing a dashboard in the Power BI Service. The identifier is in the URL.


= Other Settings for Power BI Content =
You can also configure how the content is embedded including whether to show the filter pane or page navigation along with the language (defines the language Power BI uses for localization and locale format (defines the text formatting that powerBI uses for dates, currency, etc.). Finally, you can set the default width and height for the container in pixels or as a percentage.

= Embedding Content in WordPress =
Once the Power BI content is created in WordPress, you can embed it anywhere with a shortcode. The shortcode accepts the unique identifier for the Power BI content (visible from "All Power BI Items") and an optional width and height to customize the size of the content where it is being embedded, if you want to override the default width and/or height for the content.

    [powerbi id=X width=X height=X]

You also can use a shortcode to display content based on the Power BI Embedded resource state. This shortcode allows you to show content when the resource is active and other content when the resource is paused. 

Display the content when resource is active.

    [powerbi_resource]
        Resource is active.
    [/powerbi_resource]

Display the content when resource is paused.

    [powerbi_resource state="Paused"]
        Resource is paused.
    [/powerbi_resource]

Learn more about states. [https://docs.microsoft.com/en-us/rest/api/power-bi-embedded/capacities/getdetails#state](https://docs.microsoft.com/en-us/rest/api/power-bi-embedded/capacities/getdetails#state)

== Filters ==
The plugin is able to filter *Reports* using the [https://github.com/Microsoft/PowerBI-JavaScript/wiki/Filters](Report Level Filters) API functions in PowerBI embedded. To use filters you need to pass the filter object in the querystring as a serialized JSON string.

Example 

    var relatedFilterObj = [{
            $schema : "http://powerbi.com/product/schema#basic",
            target : {
                table : "Countries",
                column : "Country",
            },
            operator : "=",
            values : [country]
            
        }
    ];

    var relatedURL = pageURL + "?filters=" +  encodeURIComponent(JSON.stringify(relatedFilterObj));

== Applying Slicers ==
The plugin can also apply Slicers before the report loads based on passing stringified JSON in the URL. Read more about [Slicers](https://github.com/Microsoft/PowerBI-JavaScript/wiki/Slicers)

**Example**

    var slicers = [
        {
            selector : {
                $schema: "http://powerbi.com/product/schema#visualSelector",
                visualName: "fee64d853d2c3e579085"
            },
            state : {
                filters : [
                    {
                        $schema: "http://powerbi.com/product/schema#basic",
                        target : {
                            table : "Tools",
                            column : "Tool"
                        },
                        operator: "In",
                        values: ["Information Operations"],
                    }
                ]
            }
        }
    ];

    var relatedURL = pageURL + "?slicers=" +  encodeURIComponent(JSON.stringify(slicers));

== Installation ==

1. Visit 'Plugins > Add New'
1. Search for 'Power BI Embedded'
1. Activate Power BI Embedded for WordPress from your Plugins page.

== Frequently Asked Questions ==

N/A

== Screenshots ==

1. Main settings screen.
2. Screen to add a Power BI item.
3. Example Power BI report.

== Changelog ==

= 1.0 =
* Initial release.

= 1.0.1 =
* Updated the Power BI JavaScript assets from v2.4.6 to v2.5.1
* Add scheduling feature to the Power BI setting page to start and stop resource automatically.
* Add fields to the Power BI setting page to configure Subscription ID, Tenant ID, Resource Group etc.
* Add shortcode to display content based on Power BI resource's capacity status.

= 1.0.2 =
* Added transparent background option. 
* Added page name configuration for reports.

= 1.1.0 = 
* Added abiltiy to use filters on report
* Updated documentation on using filters
* Increased timeout so report would still work after being open for an hour
* Updated Power BI JavaScript assets from v2.5.1 to v2.6.6
* Added url-search-params-polyfill to be backwards compatible with IE11

= 1.1.1 = 
* Added support for caching plugins (must not cache /wp-json/wp/v2/powerbi/getToken directory)
* Attempt at fixes for WP Bakery theme

= 1.1.2 = 
* More fixes for WP Bakery theme (upgrade cmb2 library)

= 1.1.3 = 
* Fix for initial setup that was causing occassional authentication errors. 
* Update Power BI library.

== Upgrade Notice ==

= 1.0.1 =
This release contains bug fixes from the Power BI library.

= 1.0.2 =
This release contains feature enhancements. 

= 1.1.0 = 
This release contains feature enhancements and bug fixes.

= 1.1.1 = 
This release contains important bug fixes. 

= 1.1.2 = 
This release contains important bug fixes. 

= 1.1.3 = 
This release contains important bug fixes. 