#TaskBreaker Group Project Management

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/118e1366-fbab-4ef8-bc07-0fffc4bc2f59/big.png)](https://insight.sensiolabs.com/projects/118e1366-fbab-4ef8-bc07-0fffc4bc2f59)

![Plugin Version](https://img.shields.io/wordpress/plugin/v/taskbreaker-project-management.svg)&nbsp;![Total Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/taskbreaker-project-management.svg)&nbsp;![Plugin Ratings](https://img.shields.io/wordpress/plugin/r/taskbreaker-project-management.svg)&nbsp;![Plugin Compatibility](https://img.shields.io/wordpress/v/taskbreaker-project-management.svg)&nbsp;![License](http://img.shields.io/:license-GPL--2.0%2B-red.svg?style=flat-square)&nbsp;

A plugin for BuddyPress that allows you to manage your projects and assign a task to each of the members of a particular group. You can set the priority for each task (*Normal*, *High*, and *Critical*). TaskBreaker also sends email notifications to members whenever there are new tasks available, or there are new updates.

####Up and Running
 - [Setup and Installation](https://dunhakdis.com/taskbreaker-group-project-management/setup-and-installation/)
	- [Download](https://dunhakdis.com/taskbreaker-group-project-management/setup-and-installation/download/)
	- [Installation](https://dunhakdis.com/taskbreaker-group-project-management/setup-and-installation/installation/)
	- [BuddyPress Configuration](https://dunhakdis.com/taskbreaker-group-project-management/setup-and-installation/buddypress-configuration/)
 - [Features and Usage](https://dunhakdis.com/taskbreaker-group-project-management/features-and-usage/)
	 - [Introduction to Projects](https://dunhakdis.com/taskbreaker-group-project-management/features-and-usage/introduction-to-projects/)
	 - [Introduction to Tasks](https://dunhakdis.com/taskbreaker-group-project-management/features-and-usage/introduction-to-tasks/)

Click [here](https://dunhakdis.com/taskbreaker-group-project-management/) to see the full documentation.
####Screenshots

![TaskBreaker Group Project Management](https://dunhakdis.com/wp-content/uploads/2017/04/TaskBreaker-Documentation-Screenshot.png)

###FAQ
####1: Cannot access 'Projects Directory' or 'Project Pages'

This issue commonly occurs when you have not enabled the '*BuddyPress Groups Component*' in your  '*BuddyPress Settings*' page that would accommodate the '*Projects Component*' properly on your site.

#####Solve the issue by following the steps below:
 1. Go to your '*WordPress Dashboard*' > '*Settings*' > '*BuddyPress*.'
 2. In the '*BuddyPress Settings*' page, go to the '*Components*' tab and check the checkbox labeled '*User Groups*' in the '*Component lists*.'
 3. Finally, click the '*Save Settings*' button.

####2: Fatal error in User Profile Page

    Fatal error: Call to undefined function groups_get_user_groups() in C:\xampp\htdocs\dsc-test\wp-content\plugins\taskbreaker-project-management\core\functions.php on line 406`

Solve this issue by enabling the Groups Component:

 1. Go to your '*WordPress Dashboard*' > '*Settings*' > '*BuddyPress*.'
 2. In the '*BuddyPress Settings*' page, go to the '*Components*' tab and check the checkbox labeled '*User Groups*' in the '*Component lists*.'
 3. Finally, click the '*Save Settings*' button.

#### 3. Project's Directory is empty (e.g. http://localhost/projects)

This issue will likely to occur when you forgot to assign a page for your 'Projects'. Solve this by following the steps below:

 1. Go to your '*WordPress Dashboard*' > '*Settings*' > '*BuddyPress*.'
 2. In the '*BuddyPress Settings*' page, go to the '*Pages*' tab and go to the 'Directories' section.
 3. In the '*Directories*' section, go to the '*Projects*' setting drop-down and select a page in the drop-down selection.
 4. Finally, click the '*Save Settings*' button.

####Known Issues

- Incompatibility with **[Yoast SEO](https://wordpress.org/plugins/wordpress-seo/)** - Will be fixed in next release cycle

####Contributions

Contributions are highly welcome, and there are various ways you can contribute:

- Spotted a bug or an issue? Share it with us on [GitHub](https://github.com/codehaiku/taskbreaker-project-management/issues/new).
- Want to share your bug fixes or share ideas for the new features? Send us a Pull Request
- Send us feedback and suggestions for enhancements, share it with the team [team ](https://github.com/codehaiku/taskbreaker-project-management/issues/new)
- Translators are welcome.

####License

TaskBreaker is licensed under [GNU General Public License 2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
