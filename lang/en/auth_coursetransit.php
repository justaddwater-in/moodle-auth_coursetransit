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

$string['addsite'] = 'Add Site';
$string['allowedservices'] = 'Allowed Services';
$string['apierror'] = 'Request failed. Please contact the administrator.';
$string['apiuser_help'] = 'This user will be used for secure API communication between WordPress and Moodle. It is recommended to select an admin or a dedicated integration user.';
$string['backtosites'] = 'Back to Sites';
$string['courseservices'] = 'Course Services';
$string['deletesiteconfirm'] = 'Are you sure you want to delete this site?';
$string['emailmissing'] = 'Email missing in token';
$string['enrolmentservices'] = 'Enrolment Services';
$string['generate'] = 'Generate';
$string['generatedtoken'] = 'Generated Token';
$string['gotodashboard'] = 'Go to Dashboard';
$string['gotowordpress'] = 'Go to WordPress';
$string['invalidaudience'] = 'Invalid audience';
$string['invalidissuer'] = 'Invalid issuer';
$string['invalidpayload'] = 'Invalid token payload';
$string['invalidsignature'] = 'Invalid token signature';
$string['invalidtoken'] = 'Invalid token structure';
$string['notoken'] = 'No token received';
$string['pluginname'] = 'CourseTransit';
$string['privacy:metadata:email'] = 'The user email is sent to external API.';
$string['privacy:metadata:externalpurpose'] = 'Data is sent for plugin tracking, analytics, or activation.';
$string['privacy:metadata:firstname'] = 'The user first name is sent to external API.';
$string['privacy:metadata:lastname'] = 'The user last name is sent to external API.';
$string['privacy:metadata:api_logs'] = 'Logs of API requests made by CourseTransit.';
$string['privacy:metadata:ipaddress'] = 'The IP address of the request origin.';
$string['privacy:metadata:message'] = 'Log message which may contain request details.';
$string['privacy:metadata:origin'] = 'Origin of the API request.';
$string['savechanges'] = 'Save Changes';
$string['selectapiuser'] = 'API User';
$string['selectsiteinfo'] = 'Please select a WordPress site to configure its services.';
$string['selectuser'] = 'Select User';
$string['services'] = 'Services';
$string['services_desc'] = 'Select which Moodle features this site can access via API.';
$string['services_title'] = 'Manage API Access';
$string['servicesupdated'] = 'Services updated successfully for this site.';
$string['sitename'] = 'Site Name';
$string['sitename_help'] = 'Enter a friendly name for your WordPress website. Example: Main Academy Website';
$string['sites'] = 'WordPress Sites';
$string['sites_desc'] = 'Register and manage WordPress sites that are allowed to connect to this Moodle site.';
$string['sites_desc_token'] = 'Each site is issued a unique token used for secure communication.';
$string['sites_title'] = 'WordPress Sites';
$string['siteurl'] = 'WordPress Site URL';
$string['siteurl_help'] = 'Enter your WordPress website domain or URL. Example: example.com or https://example.com';
$string['tab_services'] = 'Services';
$string['tab_sites'] = 'Sites';
$string['tab_token'] = 'Configuration';
$string['technicalusernotset'] = 'API user is not configured.';
$string['token'] = 'Web service token';
$string['unauthorizedsite'] = 'Unauthorized site';
$string['userservices'] = 'User Services';
$string['wordpresssite'] = 'WordPress Site';
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
$string['settingscarddesc'] = 'Connect WordPress websites with Moodle, configure secure integrations, and manage API access from the CourseTransit dashboard.';
$string['opendashboard'] = 'Open Dashboard';
$string['launchsetupwizard'] = 'Launch Setup Wizard';
$string['setupwarningtitle'] = 'Setup incomplete';
$string['setupwarningdesc'] = 'Complete the setup wizard to configure API access and register your first WordPress website.';
$string['setupcompletetitle'] = 'CourseTransit is configured';
$string['setupcompletedesc'] = 'Your integration platform is active and ready to manage connected websites and services.';
$string['sitedomainexists'] = 'This WordPress site is already registered.';
$string['sitecreated'] = 'WordPress site added successfully.';
$string['sitedeleted'] = 'WordPress site deleted successfully.';
$string['invalidsiteurl'] = 'Invalid WordPress site URL.';
$string['configure'] = 'Configure';
$string['coursetransitservice'] = 'CourseTransit Service';
$string['service_getsiteinfo'] = 'Get site info';
$string['service_createusers'] = 'Create users';
$string['service_deleteusers'] = 'Delete users';
$string['service_getusersbyfield'] = 'Get users by field (email, id)';
$string['service_updateusers'] = 'Update users';
$string['service_getcourses'] = 'Get courses';
$string['service_getcoursesbyfield'] = 'Get courses by field';
$string['service_getcategories'] = 'Get course categories';
$string['service_getcontents'] = 'Get course contents';
$string['service_getuserenrolments'] = 'Get user enrolments';
$string['service_getenrolledusers'] = 'Get enrolled users';
$string['service_manualenrol'] = 'Manually enrol users';
$string['service_manualunenrol'] = 'Manually unenrol users';
$string['servicefunction'] = 'Service function';
$string['payloadjson'] = 'Payload JSON';
$string['invalidpayloadformat'] = 'Invalid payload.';
$string['invalidservice'] = 'Invalid service requested.';
$string['servicenotallowed'] = 'Service not allowed.';
$string['sitenamecolumn'] = 'Site Name';
$string['domaincolumn'] = 'Domain';
$string['settingscolumn'] = 'Settings';
$string['actionscolumn'] = 'Actions';
$string['nositesregistered'] = 'No WordPress sites registered yet.';
$string['gatewayservice'] = 'CourseTransit gateway service';
$string['settingscardtitle'] = 'Authentication & Integration Platform';
$string['tokenmodaltitle'] = 'Save Your API Token';
$string['missingsiteurl'] = 'Missing site URL.';
$string['success'] = 'Success';
$string['responsejson'] = 'Response JSON';
$string['cachedef_session'] = 'Temporary session cache';
$string['privacy:metadata:sites'] = 'Stores registered WordPress websites connected to Moodle.';
$string['privacy:metadata:sitename'] = 'The display name of the registered WordPress site.';
$string['privacy:metadata:sitedomain'] = 'The domain or URL of the registered WordPress site.';
$string['dashboard'] = 'Dashboard';
$string['configuration'] = 'Configuration';
$string['summary'] = 'Summary';
$string['sso'] = 'SSO';
$string['webserviceconfiguration'] = 'Web Service Configuration';
$string['webserviceconfigurationdesc'] = 'Configure Moodle web service integration settings required for CourseTransit communication with WordPress websites.';
$string['enablerestprotocol'] = 'Enable REST Protocol';
$string['enablerestprotocoldesc'] = 'Required for secure API communication between Moodle and WordPress.';
$string['enablewebservices'] = 'Enable Web Services';
$string['enablewebservicesdesc'] = 'Enables Moodle external service APIs required for CourseTransit synchronisation.';
$string['coursetransitwebservice'] = 'CourseTransit Web Service';
$string['configured'] = 'Configured';
$string['notconfigured'] = 'Not Configured';
$string['configureautomatically'] = 'Configure Automatically';
$string['settingsupdated'] = 'Settings updated successfully.';
$string['systemstatus'] = 'System Status';
$string['restprotocol'] = 'REST Protocol';
$string['enabled'] = 'Enabled';
$string['disabled'] = 'Disabled';
$string['missing'] = 'Missing';
$string['technicaluser'] = 'Technical User';

$string['connectiondetails'] = 'Connection Details';
$string['moodleurl'] = 'Moodle URL';
$string['restendpoint'] = 'REST Endpoint';
$string['functionname'] = 'Function Name';
$string['accesstoken'] = 'Access Token';

$string['websitestatistics'] = 'Website Statistics';
$string['totalwebsites'] = 'Total Websites';
$string['activewebsites'] = 'Active Websites';
$string['disabledwebsites'] = 'Disabled Websites';
$string['apiuserinfodesc'] = ' Select the administrator account that CourseTransit will use for secure API communication and synchronization tasks. This account will be used to generate authentication tokens and manage integration requests between Moodle and WordPress.';
$string['installsetupheading'] = 'CourseTransit Installation';
$string['generated'] = 'Generated';
