<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accesstoken'] = 'Access Token';
$string['actionscolumn'] = 'Actions';
$string['activewebsites'] = 'Active Websites';
$string['addsite'] = 'Add Site';
$string['allowedservices'] = 'Allowed Services';
$string['apierror'] = 'Request failed. Please contact the administrator.';
$string['apiuser'] = 'API User';
$string['apiuser_help'] = 'This user will be used for secure API communication between WordPress and Moodle. It is recommended to select an admin or a dedicated integration user.';
$string['apiuserinfodesc'] = ' Select the administrator account that CourseTransit will use for secure API communication and synchronization tasks. This account will be used to generate authentication tokens and manage integration requests between Moodle and WordPress.';
$string['backtosites'] = 'Back to Sites';
$string['cachedef_session'] = 'Temporary session cache';
$string['configuration'] = 'Configuration';
$string['configure'] = 'Configure';
$string['configureautomatically'] = 'Configure Automatically';
$string['configured'] = 'Configured';
$string['connectiondetails'] = 'Connection Details';
$string['courseservices'] = 'Course Services';
$string['coursetransitpro'] = 'CourseTransit Pro';
$string['coursetransitservice'] = 'CourseTransit Service';
$string['coursetransitwebservice'] = 'CourseTransit Web Service';
$string['dashboard'] = 'Dashboard';
$string['deletesiteconfirm'] = 'Are you sure you want to delete this site?';
$string['disabled'] = 'Disabled';
$string['disabledwebsites'] = 'Disabled Websites';
$string['domaincolumn'] = 'Domain';
$string['emailmissing'] = 'Email missing in token';
$string['enabled'] = 'Enabled';
$string['enablerestprotocol'] = 'Enable REST Protocol';
$string['enablerestprotocoldesc'] = 'Required for secure API communication between Moodle and WordPress.';
$string['enablewebservices'] = 'Enable Web Services';
$string['enablewebservicesdesc'] = 'Enables Moodle external service APIs required for CourseTransit synchronisation.';
$string['enrolmentservices'] = 'Enrolment Services';
$string['everythinginfreeplus'] = 'Everything in free, plus';
$string['functionname'] = 'Function Name';
$string['gatewayservice'] = 'CourseTransit gateway service';
$string['generate'] = 'Generate';
$string['generated'] = 'Generated';
$string['generatedtoken'] = 'Generated Token';
$string['gotodashboard'] = 'Go to Dashboard';
$string['gotowordpress'] = 'Go to WordPress';
$string['infocollected'] = 'Information we collect';
$string['infocollected_moodle'] = 'Moodle Version';
$string['infocollected_name'] = 'Name & Email';
$string['infocollected_php'] = 'PHP Version';
$string['infocollected_plugin'] = 'Plugin Version';
$string['infocollected_url'] = 'Site URL';
$string['installsetupheading'] = 'CourseTransit Installation';
$string['invalidaudience'] = 'Invalid audience';
$string['invalidissuer'] = 'Invalid issuer';
$string['invalidpayload'] = 'Invalid token payload';
$string['invalidpayloadformat'] = 'Invalid payload.';
$string['invalidservice'] = 'Invalid service requested.';
$string['invalidsignature'] = 'Invalid token signature';
$string['invalidsiteurl'] = 'Invalid WordPress site URL.';
$string['invalidtoken'] = 'Invalid token structure';
$string['launchsetupwizard'] = 'Launch Setup Wizard';
$string['missing'] = 'Missing';
$string['missingsiteurl'] = 'Missing site URL.';
$string['moodleurl'] = 'Moodle URL';
$string['nositesregistered'] = 'No WordPress sites registered yet.';
$string['notconfigured'] = 'Not Configured';
$string['notoken'] = 'No token received';
$string['opendashboard'] = 'Open Dashboard';
$string['payloadjson'] = 'Payload JSON';
$string['payloadsecurity'] = 'The requested operation contains parameters that are not permitted by CourseTransit security policy.';
$string['pluginname'] = 'CourseTransit';
$string['privacy:metadata:api_logs'] = 'Logs of API requests made by CourseTransit.';
$string['privacy:metadata:email'] = 'The user email is sent to external API.';
$string['privacy:metadata:externalpurpose'] = 'Data is sent for plugin tracking, analytics, or activation.';
$string['privacy:metadata:firstname'] = 'The user first name is sent to external API.';
$string['privacy:metadata:ipaddress'] = 'The IP address of the request origin.';
$string['privacy:metadata:lastname'] = 'The user last name is sent to external API.';
$string['privacy:metadata:message'] = 'Log message which may contain request details.';
$string['privacy:metadata:origin'] = 'Origin of the API request.';
$string['privacy:metadata:phpversion'] = 'The PHP version running on the server.';
$string['privacy:metadata:platformversion'] = 'The installed Moodle version.';
$string['privacy:metadata:pluginversion'] = 'The installed version of the CourseTransit plugin.';
$string['privacy:metadata:sitedomain'] = 'The domain or URL of the registered WordPress site.';
$string['privacy:metadata:sitename'] = 'The display name of the registered WordPress site.';
$string['privacy:metadata:sites'] = 'Stores registered WordPress websites connected to Moodle.';
$string['privacy:metadata:technicaluserid'] = 'The Moodle technical user ID associated with the registered WordPress site.';
$string['privacy:metadata:telemetry'] = 'If the administrator explicitly opts in, basic contact and site information is sent to the CourseTransit (ChargePanda) API for support and product updates.';
$string['proautosyncdesc'] = 'New courses and updates sync on their own, no manual work.';
$string['proautosynctitle'] = 'Automatic Scheduled Sync';
$string['prodevsupportdesc'] = 'Installation, configuration, and troubleshooting help.';
$string['prodevsupporttitle'] = '1 Year Developer Support';
$string['profeaturestitle'] = 'CourseTransit Pro Features';
$string['prohero_desc'] = 'Automate course syncing, manage multiple WordPress sites seamlessly, and provide a frictionless single sign-on experience.';
$string['propage_title'] = 'CourseTransit Pro Features';
$string['propriorityupdatesdesc'] = 'Get early access to new features and improvements.';
$string['propriorityupdatestitle'] = 'Priority Product Updates';
$string['proselectivesyncdesc'] = 'Choose exactly which courses appear in your storefront.';
$string['proselectivesynctitle'] = 'Selective Course Sync';
$string['prossodesc'] = 'Learners log into WordPress once and enter Moodle without re-authenticating.';
$string['prossotitle'] = 'Single Sign-On (SSO)';
$string['responsejson'] = 'Response JSON';
$string['restendpoint'] = 'REST Endpoint';
$string['restprotocol'] = 'REST Protocol';
$string['savechanges'] = 'Save Changes';
$string['selectapiuser'] = 'API User';
$string['selectsiteinfo'] = 'Please select a WordPress site to configure its services.';
$string['selectuser'] = 'Select User';
$string['service_createusers'] = 'Create users';
$string['service_deleteusers'] = 'Delete users';
$string['service_getactivitiescompletionstatus'] = 'Get activities completion status';
$string['service_getcategories'] = 'Get course categories';
$string['service_getcontents'] = 'Get course contents';
$string['service_getcoursecompletionstatus'] = 'Get course completion status';
$string['service_getcourses'] = 'Get courses';
$string['service_getcoursesbyfield'] = 'Get courses by field';
$string['service_getenrolledusers'] = 'Get enrolled users';
$string['service_getsiteinfo'] = 'Get site info';
$string['service_getuserenrolments'] = 'Get user enrolments';
$string['service_getusersbyfield'] = 'Get users by field (email, id)';
$string['service_manualenrol'] = 'Manually enrol users';
$string['service_manualunenrol'] = 'Manually unenrol users';
$string['service_updateusers'] = 'Update users';
$string['servicefunction'] = 'Service function';
$string['servicenotallowed'] = 'Service not allowed.';
$string['services'] = 'Services';
$string['services_desc'] = 'Select which Moodle features this site can access via API.';
$string['services_title'] = 'Manage API Access';
$string['servicesupdated'] = 'Services updated successfully for this site.';
$string['settingscarddesc'] = 'Connect WordPress websites with Moodle, configure secure integrations, and manage API access from the CourseTransit dashboard.';
$string['settingscardtitle'] = 'Authentication & Integration Platform';
$string['settingscolumn'] = 'Settings';
$string['settingsupdated'] = 'Settings updated successfully.';
$string['setupcompletedesc'] = 'Your integration platform is active and ready to manage connected websites and services.';
$string['setupcompletetitle'] = 'CourseTransit is configured';
$string['setupwarningdesc'] = 'Complete the setup wizard to configure API access and register your first WordPress website.';
$string['setupwarningtitle'] = 'Setup incomplete';
$string['sitecreated'] = 'WordPress site added successfully.';
$string['sitedeleted'] = 'WordPress site deleted successfully.';
$string['sitedomainexists'] = 'This WordPress site is already registered.';
$string['sitename'] = 'Site Name';
$string['sitename_help'] = 'Enter a friendly name for your WordPress website. Example: Main Academy Website';
$string['sitenamecolumn'] = 'Site Name';
$string['sites'] = 'WordPress Sites';
$string['sites_desc'] = 'Register and manage WordPress sites that are allowed to connect to this Moodle site.';
$string['sites_desc_token'] = 'Each site is issued a unique token used for secure communication.';
$string['sites_title'] = 'WordPress Sites';
$string['siteurl'] = 'WordPress Site URL';
$string['siteurl_help'] = 'Enter your WordPress website domain or URL. Example: example.com or https://example.com';
$string['siteurlmismatch'] = 'The configured WordPress site URL does not match the site assigned to this web-service token.';
$string['sso'] = 'SSO';
$string['success'] = 'Success';
$string['summary'] = 'Summary';
$string['systemstatus'] = 'System Status';
$string['tab_services'] = 'Services';
$string['tab_sites'] = 'Sites';
$string['tab_token'] = 'Configuration';
$string['tabpro'] = 'Upgrade to Pro';
$string['technicaluser'] = 'Technical User';
$string['technicalusernotset'] = 'API user is not configured.';
$string['telemetryoptindesc'] = 'Data shared includes the administrator name and email, site URL, and the current versions of Moodle, PHP, and this plugin. You can revoke this consent at any time from these settings.';
$string['telemetryoptinheading'] = 'Help improve CourseTransit (Optional)';
$string['telemetryoptinintro'] = 'To help us provide proactive support and improve future releases, you can opt-in to share basic diagnostic information about your Moodle environment. This is completely optional, strictly confidential, and we never collect student, course, or user data.';
$string['telemetryoptinlabel'] = 'I agree to share basic site details with CourseTransit to ensure system compatibility and receive important product updates.';
$string['token'] = 'Web service token';
$string['tokenmodaltitle'] = 'Save Your API Token';
$string['totalwebsites'] = 'Total Websites';
$string['unauthorizedsite'] = 'Unauthorized site';
$string['unlockadvancedintegration'] = 'Unlock Advanced Integration';
$string['upgradenow'] = 'Upgrade Now';
$string['userservices'] = 'User Services';
$string['webserviceconfiguration'] = 'Web Service Configuration';
$string['webserviceconfigurationdesc'] = 'Configure Moodle web service integration settings required for CourseTransit communication with WordPress websites.';
$string['websitestatistics'] = 'Website Statistics';
$string['wizard_continue'] = 'Continue';
$string['wizard_finish'] = 'Finish Setup';
$string['wizard_intro'] = 'This wizard will guide you through the setup process.';
$string['wizard_site_description'] = 'Register your WordPress website to securely connect it with this Moodle platform. This website will be allowed to communicate with Moodle APIs through CourseTransit.';
$string['wizard_start'] = 'Start Setup';
$string['wizard_step'] = 'Step {$a} of 3';
$string['wizard_step_complete'] = 'Setup Complete';
$string['wizard_step_site'] = 'Register WordPress Site';
$string['wizard_step_token'] = 'Site Token';
$string['wizard_step_user'] = 'Select API User';
$string['wizard_token_important'] = 'Important';
$string['wizard_token_once'] = 'This API token will be shown only once.';
$string['wizard_token_store'] = 'Please copy and store it securely.';
$string['wizard_token_warning'] = 'If you lose this token, you will need to regenerate it.';
$string['wizard_welcome'] = 'Welcome to CourseTransit';
$string['wordpresssite'] = 'WordPress Site';
