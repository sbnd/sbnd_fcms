<a href="http://fcms.sbnd.net">
  <img src="http://fcms.sbnd.net/upload/logo.png">
</a>
# [Framework and Content Management System <br />for PHP developers](http://fcms.sbnd.net) 


SBND F&CMS is a modern object oriented PHP based framework that could be very helpful to programmers with OOP knowledge. The content management system (CMS) part could also be very useful to people without any programming skills.
<br /><br />

## Some of SBND F&CMS advantages:

**- Fast Custom Programing.**
Allows you to build and register your own components with custom functionality within minutes.
The real power of SBND F&CMS is the easy creation of such Components, having custom functionality and in accordance with your concrete project needs. 

**- Using Native PHP Classes & Functions.**
We were careful not to use any extensions that might bring difficulties for future upgrades or migrations. Of course, this guarantees the high performance too.

**- Extra Components For The Most Common Web Features.**
Available for download and ready to use in your projects. Configured within minutes by following simple installation instructions.

**- Built-In Database Engine.**
Has built-in engine for generating database tables and fields based on the structure defined for the module, so no manually creation and database tables and fields are needed. No need of knowing SQL syntax.

**- Feed Data To Your Mobile App.**
SBND F&CMS could be used as a Back End or a "feeder" for mobile or other "outside" applications. It could be configured to output data in XML or JSON format instead of the built-in HTML format.

**- Easy CMS Configuration.**
All useful settings are put in one place only. No doubts of what they mean or are used for.

**- User-Friendly Back End.**
After years of experience with working with the explicit needs of our end clients we have created the most intuitive and simple interface to work with CMS and Components features. 

**- Automated Multilingual Support.**
Ability to add unlimited number of languages by simply entering a few fields from the Back End interface. SBND F&CMS takes care of multiplying the UI for any language added as well as updating all database tables accordingly.

**- Easy To Prepare Your Own Design.**
Sample "responsive", "modern" and "mobile" design themes are available and could be very helpful in preparing custom designs ready to be used. These themes are compatible with most common web browsers and are scalable for mobile devices, tablets, etc.

**- Template Engine.**
Use a simple built-in Template Engine to apply Front End logic where needed.

**- SEO Readiness.**
The downloaded instance is ready for future SEO activities. It takes care of many SEO requirements in advance, such as user-friendly links, page titles, meta words, meta descriptions. The framework automatically generates required for the search engine robots files, sitemap files, files that help Google find the website, etc.


=========

## Ready-to-use Components:


=========
The components in the SBND F&CMS have similar function to plug-ins, modules or extensions for other popular software products. The components are pieces of software with particular functionality that are ready to register in the F&CMS. As soon as you register a component, you can start using it in your projects. The real power of the SBND F&CMS comes from the easy creation of new components and using them to customize your system in accordance with your project's needs.
Get:

**"Catalog"** Component from [https://github.com/sbnd/sbnd_fcms_catalog](https://github.com/sbnd/sbnd_fcms_catalog)

**"Events"** Component from [https://github.com/sbnd/sbnd_fcms_events](https://github.com/sbnd/sbnd_fcms_events)

**"Contact us"** Component from [https://github.com/sbnd/sbnd_fcms_contactus](https://github.com/sbnd/sbnd_fcms_contactus)

**"Registration"** Component from [https://github.com/sbnd/sbnd_fcms_register](https://github.com/sbnd/sbnd_fcms_register)

**"ForgottenPassword"** Component from [https://github.com/sbnd/sbnd_fcms_forgottenpassword](https://github.com/sbnd/sbnd_fcms_forgottenpassword)

**"Profile"** Component from [https://github.com/sbnd/sbnd_fcms_profile](https://github.com/sbnd/sbnd_fcms_profile)

**"Backup"** Component from [https://github.com/sbnd/sbnd_fcms_backup](https://github.com/sbnd/sbnd_fcms_backup)

**"Sitemap"** Component from [https://github.com/sbnd/sbnd_fcms_sitemap](https://github.com/sbnd/sbnd_fcms_sitemap)

**"FlashPlayer"** Component from [https://github.com/sbnd/sbnd_fcms_flashplayer](https://github.com/sbnd/sbnd_fcms_flashplayer)


## Getting started
 
**System Requirements**

- Apache HTTP Server version 2.0.0 or higher.
- Configure the Apache server so that it uses the 'mod_rewrite' module.
- PHP 5.2.8 or higher. 
- GD2 extension for PHP.
- MySQL 5 or higher.

**Installation**

1. Create an empty MySQL database with UTF8 collation.
2. Locate the BASIC source in the public web server directory.
3. Add write permission for the 'tmp' directory.
4. Upload the folder in your web server's document root directory.
5. Go to the 'root -> conf' directory and verify that there are two files in it and they start with the prefix 'default_'.
6. To start the installer, open the 'index.php' file in a web browser.
7. Enter information in all required fields.
Note that the password for the administrator must be at least 8 characters long and must have upper and lower case letters and numbers.
8. Go to the 'root -> conf' directory and verify that there are four files in it - two that start with the prefix 'default_' and two more, generated by the installer.
Do not remove the files that start with 'default_'.
9. Set full permissions ('777') for the directories 'tmp' and 'upload'.

**Post-Installation**

After the installation is complete, the default page of the site is opened. However, this page is initially empty. Too begin configuring your site, go to 'http://<hostname>/<projectname>/cp/index.php'. This opens the administration panel where you can configure the site. For more information, see the Documentation section.


## Documentation

File Structure - [http://fcms.sbnd.net/en/documentation/file-structure/](http://fcms.sbnd.net/en/documentation/file-structure/)

Class Reference - [http://fcms.sbnd.net/en/docs/index.html](http://fcms.sbnd.net/en/docs/index.html)

Administration Panel - [http://fcms.sbnd.net/en/documentation/how-to-work-with-the-admin/]
(http://fcms.sbnd.net/en/documentation/how-to-work-with-the-admin/)

Creating a Simple Contact Form - [http://fcms.sbnd.net/en/documentation/create-simple-contact-form/](http://fcms.sbnd.net/en/documentation/create-simple-contact-form/)

How to Create a Custom Component - [http://fcms.sbnd.net/en/documentation/how-to-create-a-custom-component/](http://fcms.sbnd.net/en/documentation/how-to-create-a-custom-component/)

How to Extend the Page Creation Component - [http://fcms.sbnd.net/en/documentation/how-to-extend-page-component/](http://fcms.sbnd.net/en/documentation/how-to-extend-page-component/)

How to Use Exception and Mobile Themes - [http://fcms.sbnd.net/en/documentation/how-to-use-exception-and-mobile-theme/](http://fcms.sbnd.net/en/documentation/how-to-use-exception-and-mobile-theme/)

Adding or Editing a Language - [http://fcms.sbnd.net/en/documentation/adding-language/](http://fcms.sbnd.net/en/documentation/adding-language/)

Changing the System Texts - [http://fcms.sbnd.net/en/documentation/changing-system-texts/](http://fcms.sbnd.net/en/documentation/changing-system-texts/)

System Settings - [http://fcms.sbnd.net/en/documentation/system-settings/](http://fcms.sbnd.net/en/documentation/system-settings/)

Creating a New Page for Your Web Site - [http://fcms.sbnd.net/en/documentation/creating-new-page/](http://fcms.sbnd.net/en/documentation/creating-new-page/)

Menu Positions - [http://fcms.sbnd.net/en/documentation/menu-positions/](http://fcms.sbnd.net/en/documentation/menu-positions/)

Managing Themes and Templates - [http://fcms.sbnd.net/en/documentation/themes-and-templates/](http://fcms.sbnd.net/en/documentation/themes-and-templates/)

Managing Users and Roles - [http://fcms.sbnd.net/en/documentation/manage-users-roles/](http://fcms.sbnd.net/en/documentation/manage-users-roles/)

Managing Components - [http://fcms.sbnd.net/en/documentation/manage-components/](http://fcms.sbnd.net/en/documentation/manage-components/)

=========

<br />
**For more information, see: [http://fcms.sbnd.net/](http://fcms.sbnd.net/)**
