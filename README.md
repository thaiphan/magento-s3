Thai's S3 Extension for Magento
===============================

[![Total Downloads](https://poser.pugx.org/thaiphan/magento-s3/d/total.svg)](https://packagist.org/packages/thaiphan/magento-s3)
[![MIT License](https://poser.pugx.org/thaiphan/magento-s3/license.svg)](https://packagist.org/packages/thaiphan/magento-s3)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/thaiphan)

Thai's S3 extension for Magento allows retailers to upload their catalogue and WYSIWYG media assets (such as images and videos) straight to Amazon S3.

**Note:** This extension is only compatible with Magento 1. Please use [Thai's S3 Extension for Magento 2](https://github.com/thaiphan/magento2-s3) for integration with Magento 2.

Digital Ocean
--------
Digital Ocean Spaces aims to be compatible with https://developers.digitalocean.com/documentation/spaces/#aws-s3-compatibility

Benefits
--------

### Easy to use

This extension is easy to use with little configuration! You only need to follow a few simple steps to get up and running!

### Sync all your media assets

The following assets are automatically saved to S3:

* Product images
* Generated thumbnails
* WYSIWYG images
* WYSIWYG videos
* Category images
* Favicon
* CAPTCHA images

### Magento can now scale horizontally

Complex file syncing between multiple servers is now a thing of the past with this extension. All your servers will be able to share the one S3 bucket as the single source of media.

### Easy integration with CloudFlare and CloudFront CDN

CloudFront and Cloudflare CDNs supports using S3 as an origin server so you can significantly reduce load on your servers.
https://miketabor.com/how-to-host-a-static-website-using-aws-s3-and-Cloudflare/

Installation
------------

See the [Installation](https://github.com/thaiphan/magento-s3/wiki/Installation) page on the wiki.

Support
-------

There's a [Troubleshooting](https://github.com/thaiphan/magento-s3/wiki/Troubleshooting) page on the wiki that I'll try and keep up to date with any issues that the community might have with the extension.

If you can't find the answer you're looking for, however, feel free to [create a GitHub issue](https://github.com/thaiphan/magento-s3/issues/new) for support regarding this extension.

FAQs
----

### Does this extension upload my log files?

No, the S3 extension only syncs across the media folder. You will need to find an alternative solution to store your log files.

### Magento is still loading images from the file system! What went wrong?

The S3 extension is built on top of the built-in database file storage, which will re-download files back onto the file system as part of a caching mechanism. Magento will then use this cached version of the image instead of using S3.

If you have enabled S3 integration then you can safely delete the images off your file system **(although please take a backup just in case)**. If you want Magento to not download files to the file system, you can configure your static media URL to point to S3 or CloudFront.

### We did something wrong and all our images are gone! Can you restore it?

I recommend taking a backup of your media files when switching file storage systems. Unfortunately, there's no way to restore images if you somehow accidentally delete them.

Success Stories
---------------

Are you a happy user of my extension? I would love to feature you! [Create a GitHub issue](https://github.com/thaiphan/magento-s3/issues/new) or [send me an email](mailto:thai@outlook.com) to discuss opportunities for cross promotion!
