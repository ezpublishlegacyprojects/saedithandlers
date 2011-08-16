create table if not exists `sa_attributemeta` (
  `contentobject_id` int(11) not null ,
  `contentclassattribute_id` int(11) not null ,
  `has_content` tinyint(1) not null ,
  primary key (`contentobject_id`, `contentclassattribute_id`) ,
  index `has_content` (`has_content` asc)
);
