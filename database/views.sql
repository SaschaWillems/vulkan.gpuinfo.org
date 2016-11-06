CREATE ALGORITHM=UNDEFINED DEFINER=`d01f5afe`@`%` SQL SECURITY DEFINER VIEW `viewExtensions` AS select `ext`.`name` AS `name`,(select count(distinct `r`.`id`) from (`reports` `r` join `deviceextensions` `de` on((`r`.`id` = `de`.`reportid`))) where (`de`.`extensionid` = `ext`.`id`)) AS `coverage` from `extensions` `ext`;

CREATE ALGORITHM=UNDEFINED DEFINER=`d01f5afe`@`%` SQL SECURITY DEFINER VIEW `viewFormats` AS select `vf`.`name` AS `name`,(select count(distinct `df`.`reportid`) from `deviceformats` `df` where ((`df`.`formatid` = `vf`.`value`) and (`df`.`lineartilingfeatures` > 0))) AS `linear`,(select count(distinct `df`.`reportid`) from `deviceformats` `df` where ((`df`.`formatid` = `vf`.`value`) and (`df`.`optimaltilingfeatures` > 0))) AS `optimal`,(select count(distinct `df`.`reportid`) from `deviceformats` `df` where ((`df`.`formatid` = `vf`.`value`) and (`df`.`bufferfeatures` > 0))) AS `buffer` from `VkFormat` `vf`;