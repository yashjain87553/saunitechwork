DROP TABLE IF EXISTS `zg_cms_block_store`;
DROP TABLE IF EXISTS `zg_cms_block`;

CREATE TABLE `zg_cms_block` (
  `block_id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT 'Block ID',
  `title` varchar(255) NOT NULL COMMENT 'Block Title',
  `identifier` varchar(255) NOT NULL COMMENT 'Block String Identifier',
  `content` mediumtext COMMENT 'Block Content',
  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Block Creation Time',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Block Modification Time',
  `is_active` smallint(6) NOT NULL DEFAULT '1' COMMENT 'Is Block Active',
  PRIMARY KEY (`block_id`),
  FULLTEXT KEY `ZG_CMS_BLOCK_TITLE_IDENTIFIER_CONTENT` (`title`,`identifier`,`content`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='CMS Block Table';


INSERT INTO `zg_cms_block` VALUES (1,'Home slide 1','sample_slide1','<p><a href=\"https://www.zirg.com/bags.html\" target=\"_self\"> <img src=\"{{media url=\"wysiwyg/infortis/banner2.jpg\"}}\" alt=\"New best seller\" /></a></p>\r\n<div class=\"caption dark2\">\r\n<h2 class=\"heading permanent\"></h2>\r\n<p class=\"permanent\"></p>\r\n</div>','2017-01-07 08:46:52','2017-08-31 11:48:38',1),(2,'Home slide 2','sample_slide2','<p><a href=\"https://www.zirg.com/haircare.html\" target=\"_self\"> <img src=\"{{media url=\"wysiwyg/Haircare.jpg\"}}\" alt=\"Create own gift set\" /></a></p>\r\n<div class=\"caption dark2\">\r\n<h2 class=\"heading permanent\"></h2>\r\n<p class=\"permanent\"></p>\r\n</div>','2017-01-07 08:46:52','2017-09-20 13:05:40',1),(3,'Home slide 3','sample_slide3','<p><a href=\"https://www.zirg.com/skincare.html\" target=\"_self\"> <img src=\"{{media url=\"wysiwyg/infortis/banner1.jpg\"}}\" alt=\"Spark a new passion\" /> </a></p>','2017-01-07 08:46:52','2017-08-31 11:52:17',1),(4,'Home slide - wide 1','sample_slide_wide1','<div style=\"text-align:center;\">\n<a href=\"{{store url=\'about-magento-demo-store\'}}\">\n\n   <img src=\"{{media url=\'wysiwyg/infortis/ultimo/slideshow/wide01.jpg\'}}\" alt=\"Sample slide\" />\n\n      <div class=\"caption light1\">\n         <h2 class=\"heading permanent\">Sample slide caption</h2>\n         <p>Sample text inside the slideshow, replace with custom content.</p>\n      </div>\n\n</a>\n</div>','2017-01-07 08:46:52','2017-01-07 08:46:52',1),(5,'Home slide - wide 2','sample_slide_wide2','<div style=\"text-align:center;\">\n<a href=\"{{store url=\'about-magento-demo-store\'}}\">\n\n   <img src=\"{{media url=\'wysiwyg/infortis/ultimo/slideshow/wide02.png\'}}\" alt=\"Sample slide\" />\n\n</a>\n</div>','2017-01-07 08:46:52','2017-01-07 08:46:52',1),(6,'Slideshow side banners','sample_slideshow_side_banners','<p><a class=\"banner fade-on-hover\" href=\"{{store url=\'fragrance-cologne.html\'}}\"> <img src=\"{{media url=\"wysiwyg/infortis/1.jpg\"}}\" alt=\"Fragrance &amp; Cologne\" width=\"274\" height=\"125\" /></a></p>\r\n<!--<div class=\"caption light1 full-width right\">\r\n<p class=\"right\">Sample caption</p>\r\n</div>-->\r\n<p><a class=\"banner fade-on-hover\" href=\"{{store url=\'bags.html\'}}\"> <img src=\"{{media url=\"wysiwyg/infortis/2.jpg\"}}\" alt=\"Bags\" width=\"274\" height=\"125\" /></a></p>\r\n<!--<div class=\"caption dark1 full-width right\">\r\n<p class=\"right\">Add custom text</p>\r\n</div>-->\r\n<p><a class=\"banner fade-on-hover\" href=\"{{store url=\'cosmetics.html\'}}\"> <img src=\"{{media url=\"wysiwyg/infortis/3.jpg\"}}\" alt=\"Cosmetics\" width=\"274\" height=\"125\" /></a></p>\r\n<!--<div class=\"caption dark3 full-width right\">\r\n<p class=\"right\">Caption sample</p>\r\n</div>-->\r\n','2017-01-07 08:46:52','2017-08-21 10:36:39',1),(7,'Custom footer links','block_footer_links','<p>This block can replace Magento\'s default footer links.</p>\r\n<ul class=\"links\">\r\n<li class=\"first\"><a title=\"My custom link\" href=\"{{store url=\'about-magento-demo-store\'}}\">Custom Link</a></li>\r\n<li class=\" last\"><a title=\"My sample link\" href=\"{{store url=\'about-magento-demo-store\'}}\">Sample Link</a></li>\r\n</ul>','2017-01-07 08:46:52','2017-01-24 06:20:50',0),(8,'Product - secondary column bottom','block_product_secondary_bottom','<div class=\"feature feature-icon-hover indent first\"><span class=\"ib ic ic-lg ic-plane\"></span>\r\n<p class=\"no-margin \">We will send this product in 2 days. <a href=\"#\">Read more...</a></p>\r\n</div>\r\n<div class=\"feature feature-icon-hover indent\"><span class=\"ib ic ic-lg ic-phone\"></span>\r\n<p class=\"no-margin \">Call us now for more info about our products.</p>\r\n</div>\r\n<div class=\"feature feature-icon-hover indent\"><span class=\"ib ic ic-lg ic-reload\"></span>\r\n<p class=\"no-margin \">Return purchased items and get all your money back.</p>\r\n</div>\r\n<div class=\"feature feature-icon-hover indent last\"><span class=\"ib ic ic-lg ic-star\"></span>\r\n<p class=\"no-margin \">Buy this product and earn 10 special loyalty points!</p>\r\n</div>\r\n<!-- Social bookmarks from http://www.addthis.com/get/sharing  -->\r\n<p></p>\r\n<!-- AddThis Button BEGIN -->\r\n<div class=\"feature-wrapper top-border\">\r\n<div class=\"addthis_toolbox addthis_default_style \"></div>\r\n<script src=\"http://s7.addthis.com/js/300/addthis_widget.js#pubid=xa-5054e6c6502d114f\" type=\"text/javascript\" xml=\"space\"></script>\r\n</div>\r\n<!-- AddThis Button END -->\r\n<p></p>','2017-01-07 08:46:52','2017-01-13 05:44:30',0),(9,'Product - primary column bottom','block_product_primary_bottom','Sample content of the static block - primary column bottom.','2017-01-07 08:46:52','2017-01-07 08:46:52',0),(10,'Custom Tab 1','block_product_tab1','<p>Custom CMS block displayed as a tab. You can use it to display info about returns and refunds, latest promotions etc. You can put your own content here: text, HTML, images - whatever you like. There are <strong>many similar blocks</strong> accross the store. All CMS blocks are editable from the admin panel.</p>\n\n<div class=\"feature indent first\">\n	<span class=\"ib ib-hover ic ic-right ic-2x\"></span>\n	<p class=\"no-margin\"><strong>Magento Community Edition is the most powerful</strong> open source e-commerce software and can be downloaded 100% for free. Developers can modify the core code, add custom features and functionality by installing extensions from Magento Connect marketplace.</p>\n</div>\n<div class=\"feature indent\">\n	<span class=\"ib ib-hover ic ic-right ic-2x\"></span>\n	<p class=\"no-margin\"><strong>Manage the fully dynamic catalog</strong> with the intutive admistrative interface. The flexible catalog system includes various options for the display of items. Magento is also integrated with a variety of major payment gateways out of the box.</p>\n</div>\n<div class=\"feature indent last\">\n	<span class=\"ib ib-hover ic ic-right ic-2x\"></span>\n	<p class=\"no-margin\"><strong>Magento is a fully global platform</strong>, allowing for the expansion of business or simply offering multiple versions of your site to meet the needs of your customers.\nTranslated into over 60 languages, supporting multiple currencies, payment methods and taxes, Magento allows for internationalization of your online stores.</p>\n</div>','2017-01-07 08:46:52','2017-01-11 05:15:06',0),(11,'Custom Tab 2','block_product_tab2','<p>Another custom CMS block displayed as a tab. You can use it to display info about returns and refunds, latest promotions etc. You can put your own content here: text, HTML, images - whatever you like. There are <strong>many similar blocks</strong> accross the store. All CMS blocks are editable from the admin panel.</p>\n\n<p>Lorem ipsum dolor sit, consectetur adipiscing elit. Etiam neque velit, blandit sed scelerisque quis. Nullam ornare enim nec justo bibendum lobortis. In eget metus.</p>','2017-01-07 08:46:52','2017-01-10 10:29:20',0),(12,'Footer primary bottom left - social links','block_footer_primary_bottom_left','<div class=\"social-links ib-wrapper--square\">\r\n	<a class=\"first\" href=\"https://twitter.com/ZirgTweet\" title=\"Follow Infortis on Twitter\">\r\n		<span class=\"ib ib-hover ic ic-lg ic-twitter\"></span>\r\n	</a>\r\n	<a href=\"https://www.facebook.com/Zirg-1003650619674444\" title=\"Join us on Facebook\">\r\n		<span class=\"ib ib-hover ic ic-lg ic-facebook\"></span>\r\n	</a>\r\n	<a href=\"https://plus.google.com/collections/featured\" title=\"Join us on Google Plus\">\r\n		<span class=\"ib ib-hover ic ic-lg ic-googleplus\"></span>\r\n	</a>\r\n	<a href=\"https://www.linkedin.com/\" title=\"Linked in\">\r\n		<span class=\"ib ib-hover ic ic-lg ic-linkedin\"></span>\r\n	</a>\r\n</div>','2017-01-07 08:46:52','2017-01-24 07:05:15',1),(13,'Footer primary bottom right - newsletter','block_footer_primary_bottom_right','{{block class=\"Magento\\Newsletter\\Block\\Subscribe\" name=\"home.form.subscribe\" template=\"Magento_Newsletter::subscribe.phtml\"}}','2017-01-07 08:46:52','2017-01-24 06:59:44',0),(14,'Footer column 1','block_footer_column1','<div class=\"mobile-collapsible\">\n	<h6 class=\"block-title heading\">About Ultimo</h6>\n\n	<div class=\"block-content\">\n\n		<img src=\"{{media url=\"wysiwyg/infortis/ultimo/custom/logo.png\"}}\" alt=\"Ultimo Theme\" />\n		<div class=\"feature first last\">\n			<p>Ultimo is a premium Magento theme with advanced admin module. It’s extremely customizable, easy to use and fully responsive.</p>\n			<p>Suitable for every type of store. Great starting point for your custom projects.</p>\n			<h5><a class=\"go\" href=\"http://themeforest.net/item/ultimo-fluid-responsive-magento-theme/3231798?ref=infortis\">Buy this theme</a></h5>\n		</div>\n\n	</div>\n</div>','2017-01-07 08:46:52','2017-01-07 08:46:52',0),(15,'Footer column 2','block_footer_column2','<div class=\"mobile-collapsible\">\n\n	<h6 class=\"block-title heading\">Theme Features</h6>\n	<div class=\"block-content\">\n	\n		<ul class=\"bullet\">\n			<li><a href=\"{{store url=\'ultimo-responsive-magento-theme\'}}\">Theme Features</a></li>\n			<li><a href=\"{{store url=\'typography\'}}\">Typography</a></li>\n			<li><a href=\"#\">Some Sample Link</a></li>\n			<li><a href=\"#\">Meat Our Best Partners</a></li>\n			<li><a href=\"#\">Latest Work Samples</a></li>\n			<li><a href=\"#\">Our Other Projects</a></li>\n			<li><a href=\"#\">One Click To Join Us</a></li>\n			<li><a href=\"#\">Follow Us On Twitter</a></li>\n			<li><a href=\"http://infortis-themes.com/\">Magento Themes</a></li>\n			<li><a href=\"#\">Ecommerce</a></li>\n		</ul>\n\n	</div>\n\n</div>','2017-01-07 08:46:52','2017-01-07 08:46:52',0),(16,'Footer column 3','block_footer_column3','<div class=\"mobile-collapsible\">\n\n	<h6 class=\"block-title heading\">Key Features</h6>\n	<div class=\"block-content\">\n		<div class=\"feature feature-icon-hover indent first\">\n			<span class=\"ib ic ic-char\">1</span>\n			<p class=\"no-margin\">Unlimited colors, hundreds of customizable elements</p>\n		</div>\n		<div class=\"feature feature-icon-hover indent\">\n			<span class=\"ib ic ic-char\">2</span>\n			<p class=\"no-margin \">Customizable responsive layout based on fluid grid</p>\n		</div>\n		<div class=\"feature feature-icon-hover indent\">\n			<span class=\"ib ic ic-char\">3</span>\n			<p class=\"no-margin \">50+ placeholders to display custom content</p>\n		</div>\n		<div class=\"feature feature-icon-hover indent\">\n			<span class=\"ib ic ic-char\">4</span>\n			<p class=\"no-margin \">Create your custom sub-themes (variants)</p>\n		</div>\n	</div>\n\n</div>','2017-01-07 08:46:52','2017-01-07 08:46:52',0),(17,'Footer column 4','block_footer_column4','<div class=\"mobile-collapsible\">\n\n		<h6 class=\"block-title heading\">Company Info</h6>\n		<div class=\"block-content\">\n			<div class=\"feature feature-icon-hover indent first\">\n				<span class=\"ib ic ic-phone ic-lg\"></span>\n				<p class=\"no-margin \">Call Us +001 555 801<br/>Fax +001 555 802</p>\n			</div>\n			<div class=\"feature feature-icon-hover indent\">\n				<span class=\"ib ic ic-mobile ic-lg\"></span>\n				<p class=\"no-margin \">+77 123 1234<br/>+42 98 9876</p>\n			</div>\n			<div class=\"feature feature-icon-hover indent\">\n				<span class=\"ib ic ic-letter ic-lg\"></span>\n				<p class=\"no-margin \">boss@example.com<br/>me@example.com</p>\n			</div>\n			<div class=\"feature feature-icon-hover indent last\">\n				<span class=\"ib ic ic-skype ic-lg\"></span>\n				<p class=\"no-margin \">Skype: samplelogin<br/>skype-support</p>\n			</div>\n		</div>\n\n</div>','2017-01-07 08:46:52','2017-01-07 08:46:52',0),(18,'Footer column 5','block_footer_column5','<h6 class=\"heading\">Sample Column</h6>\n<ul>\n	<li><a href=\"#\">Responsive Theme</a></li>\n	<li><a href=\"#\">Magento Extensions</a></li>\n	<li><a href=\"#\">Store Overview</a></li>\n	<li><a href=\"#\">Buy This</a></li>\n	<li><a href=\"#\">Sample Link</a></li>\n	<li><a href=\"#\">Some Link</a></li>\n	<li><a href=\"#\">Link Example</a></li>\n</ul>','2017-01-07 08:46:52','2017-01-07 08:46:52',0),(19,'Footer column 6','block_footer_column6','<p><img alt=\"\" /></p>\r\n<div class=\"foot_main\">\r\n<div class=\"grid12-3\">\r\n<div class=\"mobile-collapsible foot_had1\">\r\n<h6 class=\"block-title heading foot_had\">We&rsquo;re Here for You</h6>\r\n<div class=\"block-content\"><img class=\"mobile_witele\" src=\"{{media url=\"wysiwyg/infortis/contact-us1.png\"}}\" alt=\"Zirg Contact-Us\" /> <a class=\"mobile_tele\" href=\"tel:8559329474\"><img src=\"{{media url=\"wysiwyg/infortis/contact-us1.png\"}}\" alt=\"Zirg Contact-Us\" /></a>\r\n<div class=\"feature first last\">\r\n<h6 class=\"block-title heading\" style=\"color: #ea8f68;\"><img style=\"vertical-align: middle;\" src=\"{{media url=\"wysiwyg/infortis/mail.png\"}}\" alt=\"\" />Send us an email</h6>\r\n<p><a href=\"mailto:customercare@zirg.com\">customercare@zirg.com,</a><a class=\"info_mail\" href=\"mailto:customercare@zirg.com\">info@zirg.com</a></p>\r\n<!--<p><img style=\"margin: 9px 3px 6px 4px;\" src=\"{{media url=\"wysiwyg/infortis/live-chat.jpg\"}}\" alt=\"\" /></p>--></div>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"grid12-3\">\r\n<div class=\"mobile-collapsible foot_had1\">\r\n<h6 class=\"block-title heading foot_had\">Customer Service</h6>\r\n<div class=\"block-content\">\r\n<ul class=\"disc\">\r\n<li><a href=\"{{store url=\'shipping-information\'}}\">Shipping Information</a></li>\r\n<li><a href=\"{{store url=\'international-shipping\'}}\">International Shipping</a></li>\r\n<li><a href=\"{{store url=\'return-policy\'}}\">Return Policy</a></li>\r\n<li><a href=\"{{store url=\'shopping-on-zirg\'}}\">Shopping On Zirg</a></li>\r\n<li><a href=\"{{store url=\'zirg-club-benefits\'}}\">Zirg Club</a></li>\r\n<li><a href=\"{{store url=\'giftregistry/guest/search\'}}\">Gift Cards</a></li>\r\n<li><a href=\"{{store url=\'zirg-daily-deal\'}}\">Zirg Daily Deal</a></li>\r\n<li><a href=\"{{store url=\'contact\'}}\">Contact Us</a></li>\r\n<li><a href=\"{{store url=\'faqs\'}}\">FAQs</a></li>\r\n</ul>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"grid12-3\">\r\n<div class=\"mobile-collapsible foot_had1\">\r\n<h6 class=\"block-title heading foot_had\">My Account</h6>\r\n<div class=\"block-content\">\r\n<ul class=\"disc\">\r\n<li><a href=\"{{store url=\"sales/guest/form\"}}\">Orders Status &amp; History</a></li>\r\n<li><a href=\"{{store url=\"customer/account\"}}\">Edit/ Manage Account Information</a></li>\r\n<li><a href=\"{{store url=\"customer/address\"}}\">Edit/ Manage Address Book</a></li>\r\n<li><a href=\"{{store url=\"customer/account/forgotpassword\"}}\">Forgot Password</a></li>\r\n<li><a href=\"{{store url=\"wishlist\"}}\">My Wishlist</a></li>\r\n<li><a href=\"{{store url=\"giftregistry/guest/search/\"}}\">My Gift Registry</a></li>\r\n<li><a href=\"{{store url=\"review/customer\"}}\">My Reviews</a></li>\r\n<li><a href=\"{{store url=\"newsletter/manage\"}}\">Newsletter Subscription</a></li>\r\n</ul>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"grid12-3\">\r\n<div class=\"mobile-collapsible foot_had1\">\r\n<h6 class=\"block-title heading foot_had\">About Zirg</h6>\r\n<div class=\"block-content\">\r\n<ul class=\"disc\">\r\n<ul class=\"disc about_ul\">\r\n<li><a href=\"{{store url=\"profile\"}}\">Company Profile</a></li>\r\n<li><a href=\"{{store url=\"guarantee\"}}\">Our Guarantee</a></li>\r\n<li><a href=\"{{store url=\"news\"}}\">Press</a></li>\r\n<li><a href=\"{{store url=\"wholesale\"}}\">Wholesale Information</a></li>\r\n<li><a href=\"{{store url=\"business-opportunities\"}}\">Business Opportunities</a></li>\r\n<li><a href=\"{{store url=\"careers\"}}\">Careers</a></li>\r\n</ul>\r\n</ul>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"grid12-12\">\r\n<div class=\"footer-primary-bottom-spacing inner cat_foot\">{{block class=\"Jvs\\Qes\\Block\\Index\\Index\" template=\"index/footer_categories.phtml\"}}\r\n<div class=\"grid12-3\"><a href=\"{{store url=\"daily-deals.html\"}}\"><img src=\"{{media url=\"wysiwyg/infortis/zirg_footer_img.png\"}}\" alt=\"Zirg Daily Deals\" /></a></div>\r\n</div>\r\n</div>\r\n<div class=\"item item-left\">\r\n<ul class=\"footer links\">\r\n<li class=\"nav item\"><a href=\"{{store url=\"terms-of-use/\"}}\">Terms of Use</a></li>\r\n<li class=\"nav item\"><a href=\"{{store url=\"privacy-policy/\"}}\">Privacy Policy </a></li>\r\n<li class=\"nav item\"><a href=\"{{store url=\"sitemap/\"}}\" data-action=\"advanced-search\">Site Map</a></li>\r\n</ul>\r\n</div>\r\n<!-- end: inner-container -->\r\n<p></p>\r\n<!--\r\n   <div class=\"clearfix\"></div>\r\n   <br/>\r\n   <br/>\r\n   <div class=\"grid12-12\" style=\"text-align: center;\">\r\n   \r\n   	<div class=\"social-links\" style=\"display: inline-block; float: none;\">\r\n   		<a class=\"first\" href=\"http://twitter.com/infortis\" title=\"Follow Infortis on Twitter\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-twitter\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Join us on Facebook\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-facebook\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Join us on Google Plus\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-googleplus\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Youtube\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-youtube\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Vimeo\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-vimeo\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Instagram\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-instagram\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Pinterest\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-pinterest\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Linked in\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-linkedin\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"VKontakte\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-vk\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Renren\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-renren\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Flickr\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-flickr\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Behance\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-behance\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Xing\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-xing\"></span>\r\n   		</a>\r\n   		<a href=\"#\" title=\"Blogger\">\r\n   			<span class=\"ib ib-hover ic ic-lg ic-blogger\"></span>\r\n   		</a>\r\n   	</div>\r\n   \r\n   </div>\r\n   <div class=\"clearfix\"></div>\r\n   <br/>\r\n   -->\r\n<style xml=\"space\"><!--\r\n      .footer-top-container { background-color: #ffffff; display: none; }\r\n--></style>','2017-01-07 08:46:52','2018-05-08 06:00:52',1),(20,'Footer payment','block_footer_payment','<p><img title=\"Sample image with payment methods\" src=\"{{media url=\"wysiwyg/infortis/pay_pal.png\"}}\" alt=\"Payment methods\" /></p>\r\n','2017-01-07 08:46:52','2017-01-10 09:16:05',1),(21,'TESTER1','sample_TESTER1','<div style=\"\n    background-color: #FFF4F4;\n    padding: 10px 12px;\n\">\ntester 1\n</div>','2017-01-07 08:46:52','2017-01-07 08:46:52',1),(22,'TESTER2','sample_TESTER2','<div style=\"\n    background-color: #F4FFFA;\n    padding: 10px 12px;\n\">\ntester 2\n</div>','2017-01-07 08:46:52','2017-01-07 08:46:52',1),(23,'Main menu - custom links','block_nav_links','<ul>\r\n<li class=\"nav-item level0 level-top right\"><a class=\"level-top right_menu \" title=\"Daily Deals\" href=\"{{store direct_url=\'daily-deals.html\'}}\"> <img src=\"{{media url=\"wysiwyg/infortis/daily-deals.png\"}}\" alt=\"Daily-deals\" /> </a></li>\r\n</ul>\r\n<style xml=\"space\"><!--\r\n#bitnami-banner .bitnami-corner-image-div .bitnami-corner-image{\r\n   display:none;\r\n}\r\n.nav-item.level0.nav-9.level-top.last.nav-item--parent.classic.nav-item--only-subcategories.parent {\r\n    display: none;\r\n}\r\n--></style>','2017-01-07 08:46:52','2018-02-27 10:32:07',1),(24,'Custom','block_nav_dropdown','<div class=\"grid-container-spaced\">\r\n<div class=\"grid12-3\">\r\n<h2>Responsive Magento Theme</h2>\r\n<p>Ultimo is a premium Magento theme with advanced admin module. It\'s extremely customizable and fully responsive. Can be used for every type of store.</p>\r\n<h5><a class=\"go\" style=\"color: red;\" href=\"http://themeforest.net/item/ultimo-fluid-responsive-magento-theme/3231798?ref=infortis\">Buy this Magento theme</a></h5>\r\n</div>\r\n<div class=\"grid12-3\"><a href=\"{{store url=\'ultimo-responsive-magento-theme\'}}\"> <img class=\"fade-on-hover\" src=\"{{media url=\'wysiwyg/infortis/ultimo/menu/custom/01.png\'}}\" alt=\"Magento CMS blocks\" /> </a>\r\n<h4 class=\"heading\">50+ CMS blocks</h4>\r\n<p>You can use CMS blocks as content placeholders to display custom content in almost every part of the store. Import sample CMS blocks from the demo.</p>\r\n<a class=\"go\" href=\"{{store url=\'ultimo-responsive-magento-theme\'}}\">See all features</a></div>\r\n<div class=\"grid12-3\"><a href=\"{{store url=\'ultimo-responsive-magento-theme\'}}\"> <img class=\"fade-on-hover\" src=\"{{media url=\'wysiwyg/infortis/ultimo/menu/custom/02.png\'}}\" alt=\"Magento theme documentation\" /> </a>\r\n<h4 class=\"heading\">190-pages documentation</h4>\r\n<p>The best Magento theme documentation on ThemeForest. It also describes selected Magento features which you need to know when starting to work with Magento.</p>\r\n<a class=\"go\" href=\"{{store url=\'ultimo-responsive-magento-theme\'}}\">See all features</a></div>\r\n<div class=\"grid12-3\"><a href=\"{{store url=\'ultimo-responsive-magento-theme\'}}\"> <img class=\"fade-on-hover\" src=\"{{media url=\'wysiwyg/infortis/ultimo/menu/custom/03.png\'}}\" alt=\"Create Magento sub-themes\" /> </a>\r\n<h4 class=\"heading\">Easy to customize</h4>\r\n<p>Use Ultimo as a starting point for your custom projects. Unlike many other themes, Ultimo lets you create multiple custom sub-themes (theme variants) for your clients.</p>\r\n<a class=\"go\" href=\"{{store url=\'ultimo-responsive-magento-theme\'}}\">See all features</a></div>\r\n</div>','2017-01-07 08:46:52','2017-01-20 10:10:46',0),(25,'Custom Top Links (to replace Magento\'s default Top Links)','block_header_top_links','<ul class=\"links\">\r\n<li class=\"first\"><a title=\"Sample\" href=\"#\">Sample</a></li>\r\n<li><a title=\"Custom\" href=\"#\">Custom</a></li>\r\n<li class=\"last\"><a title=\"Links\" href=\"#\">Links</a></li>\r\n</ul>','2017-01-07 08:46:52','2017-08-21 08:13:31',0),(26,'Header top left','block_header_top_left','<div class=\"hide-below-960\" title=\"You can put here a phone number or some additional help info\"><span class=\"ic ic-lg ic-phone\"></span> Call (855) 932-9474</div>','2017-01-07 08:46:52','2017-01-09 13:36:56',1),(27,'Header top left - custom links','sample_header_top_left','<div class=\"links-wrapper-separators\">\n\n   <ul class=\"links\">\n      <li class=\"first\">\n         <a href=\"http://ultimo.infortis-themes.com/demo/select-demo/\" title=\"See more demos\">All demos</a>\n      </li>\n      <li class=\"hide-below-768\">\n         <a href=\"{{store url=\'ultimo-responsive-magento-theme\'}}\" title=\"See the list of all features\">Features</a>\n      </li>\n      <li class=\"last hide-below-480\">\n         <a href=\"http://themeforest.net/item/ultimo-fluid-responsive-magento-theme/3231798?ref=infortis\" title=\"Click to buy this theme\">Buy me!</a>\n      </li>\n   </ul>\n\n</div>','2017-01-07 08:46:52','2017-01-07 08:46:52',1),(28,'Header top right','block_header_top_right','Sample content of the top right static block block_header_top_right','2017-01-07 08:46:52','2017-01-07 08:46:52',0),(29,'Header top right - custom links','sample_header_top_right','<div class=\"links-wrapper-separators\">\n\n   <ul class=\"links\">\n      <li class=\"first\">\n         <a href=\"http://ultimo.infortis-themes.com/demo/select-demo/\" title=\"See more demos\">All demos</a>\n      </li>\n      <li class=\"last\">\n         <a href=\"http://themeforest.net/item/ultimo-fluid-responsive-magento-theme/3231798?ref=infortis\" title=\"Click to buy this theme\">Buy me!</a>\n      </li>\n   </ul>\n\n</div>','2017-01-07 08:46:52','2017-01-07 08:46:52',1),(30,'Test Deeshit Block','deeshit_block','<p>{{block class=\"Infortis\\Base\\Block\\Product\\ProductList\\Featured\" template=\"product/list_featured_slider.phtml\" category_id=\"4\" product_count=\"8\" breakpoints=\"[0, 1], [320, 2], [480, 3], [768, 2], [992, 2], [1200, 3]\" timeout=\"4000\" centered=\"1\" hide_button=\"1\" block_name=\"NEW ARRIVALS\"}}</p>','2017-01-11 06:41:07','2017-01-11 06:58:53',1),(31,'Brandpage','brandpage','hiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii','2017-01-21 10:52:01','2017-01-25 04:23:36',1),(32,'Product list sidebar','productlistsidebar','{{widget type=\"Magento\\Reports\\Block\\Product\\Widget\\Viewed\" page_size=\"5\" template=\"widget/viewed/content/viewed_list.phtml\"}}','2017-02-01 09:42:20','2017-02-01 09:42:20',1),(33,'Join us with banner','joinuswithbanner','<div class=\"page-banners grid-container bottum_add\">\r\n<div class=\"grid12-4 mobile-grid banner\"><a title=\"100% Genuine brands\" href=\"https://www.zirg.com/top-brands/\"> <img src=\"{{media url=\"wysiwyg/infortis/genuine_tab_1.png\"}}\" alt=\"100% Genuine brands\" /> </a></div>\r\n<div class=\"grid12-4 mobile-grid banner\"><a title=\"100% Secure Shopping\" href=\"https://www.zirg.com/shopping-on-zirg/\"> <img src=\"{{media url=\"wysiwyg/infortis/secure_tab_1.png\"}}\" alt=\"100% Secure Shopping\" /> </a></div>\r\n<div class=\"grid12-4 mobile-grid banner\"><a title=\"100% Setisfection\" href=\"https://www.zirg.com/guarantee/\"> <img src=\"{{media url=\"wysiwyg/infortis/satisfaction_tab.png\"}}\" alt=\"100% Setisfection \" /> </a></div>\r\n</div>\r\n<div class=\"page-banners grid-container bottum_add2\" style=\"margin: 0px 0px -20px;\">\r\n<div class=\"grid12-4 mobile-grid banner\"><a title=\"Join zoirg club\" href=\"https://www.zirg.com/zirg-club-benefits/\"> <img src=\"{{media url=\"wysiwyg/infortis/join_zirg.jpg\"}}\" alt=\"Join zoirg club\" /> </a></div>\r\n<div class=\"grid12-7 mobile-grid banner join_main\">\r\n<div class=\"grid12-9 join_img\">\r\n<h3 style=\"margin: 10px 0; font-family: Tahoma;\">Join Our Mailing List</h3>\r\n<h2 style=\"color: #ea8f68; margin: 10px 0; font-family: Tahoma; font-size: 17px;\">Sign up to receive special offers and promotions</h2>\r\n<div class=\"block newsletter\">\r\n<div class=\"title\"><strong>Newsletter</strong></div>\r\n<div class=\"content\">{{block class=\"Magento\\Newsletter\\Block\\Subscribe\" name=\"home.form.subscribe\" template=\"Magento_Newsletter::subscribe.phtml\"}}</div>\r\n</div>\r\n</div>\r\n<div class=\"grid12-3 join_socail\">\r\n<div class=\"item item-left\">\r\n<div class=\"social-links ib-wrapper--square new_social\"></div>\r\n</div>\r\n</div>\r\n<div class=\"item item-left\">\r\n<div class=\"social-links ib-wrapper--square new_social\">\r\n<div class=\"grid12-3 join_socail\">\r\n<div class=\"item item-left\">\r\n<div class=\"social-links ib-wrapper--square new_social\"></div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>\r\n<style xml=\"space\"><!--\r\n.comparison > .table-comparison > tbody > tr > td > strong {\r\n    height: 55px;\r\n}\r\n.account-nav > ul > li:nth-child(5) {\r\n    display: none;\r\n}\r\n.form-wishlist-items > .actions-toolbar > .primary > .tocart {\r\n    display: none;\r\n}\r\n.block.newsletter {\r\n    margin: 0;\r\n    width: 500px;\r\n}\r\n.page-banners .banner .first {\r\n    margin: 6px 0 0 42px;\r\n}\r\n--></style>','2017-02-01 11:28:42','2018-05-18 10:26:54',1),(34,'Gift Registry','gift_registry','</p><img src=\"{{media url=\"wysiwyg/images-11_1.jpg\"}}\" alt=\"\" />','2017-02-04 04:11:14','2017-02-18 12:51:04',1),(35,'home_slider3','home_slider3','<p><a href=\"https://www.zirg.com/fragrance-cologne.html\" target=\"_self\"> <img src=\"{{media url=\"wysiwyg/perfume-2.jpg\"}}\" alt=\"New best seller\" width=\"1920\" height=\"740\" /></a></p>\r\n<div class=\"caption dark2\">\r\n<h2 class=\"heading permanent\"></h2>\r\n<p class=\"permanent\"></p>\r\n</div>','2017-08-10 09:04:02','2017-08-10 09:04:02',1);

CREATE TABLE `zg_cms_block_store` (
  `block_id` smallint(6) NOT NULL COMMENT 'Block ID',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
  PRIMARY KEY (`block_id`,`store_id`),
  KEY `ZG_CMS_BLOCK_STORE_STORE_ID` (`store_id`),
  CONSTRAINT `ZG_CMS_BLOCK_STORE_BLOCK_ID_CMS_BLOCK_BLOCK_ID` FOREIGN KEY (`block_id`) REFERENCES `zg_cms_block` (`block_id`) ON DELETE CASCADE,
  CONSTRAINT `ZG_CMS_BLOCK_STORE_STORE_ID_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `zg_store` (`store_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='CMS Block To Store Linkage Table';


INSERT INTO `zg_cms_block_store` VALUES (1,0),(2,0),(3,0),(4,0),(5,0),(6,0),(7,0),(8,0),(9,0),(10,0),(11,0),(12,0),(13,0),(14,0),(15,0),(16,0),(17,0),(18,0),(19,0),(20,0),(21,0),(22,0),(23,0),(24,0),(25,0),(26,0),(27,0),(28,0),(29,0),(30,0),(31,0),(32,0),(33,0),(34,0),(35,0);