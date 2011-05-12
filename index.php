<?php

require_once('lib/Savant3.php');
$tpl = new Savant3();

$tpl->title = 'Mawari v0.1-alpha';
$tpl->introduction = "<p>Welcome! I will be updating this pageâ... whenever it strikes my fancy, I guess. This page is not meant to be a freebie mp3 download page, but 
to try new samplers, find more music and open up your horizons! I listen to a lot of weird crap, so you're bound to come across something you will like, eventually. 
I hope.</p>

<p>A lot of these songs have been/are/will be inspirations and/or emotional encouragement with my artwork so hence why I think they deserve to be here, I won't put up 
stupid shit without any reason, so please don't hate on my musical tastes!</p>";

$tpl->songlist = array(
	array(
		'title' => 'Beirut - My Wife, Lost In The Woods',
		'description' => '',
		'url' => ''
	)
);

$tpl->display('b
