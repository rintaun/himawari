<?php
require_once('lib/Savant3.php');
$tpl = new Savant3();

$TEMPLATE = 'kyrie';

$tpldir = 'tpl/' . $TEMPLATE . '/';

$tpl->tpldir = $tpldir;

$tpl->title = 'Mawari v0.1-alpha';
$tpl->introduction = "Welcome! I will be updating this page... whenever it strikes my fancy, I guess. This page is not meant to be a freebie mp3 download page, but to try new samplers, find more music and open up your horizons! I listen to a lot of weird crap, so you're bound to come across _something_ you will like, eventually. I hope.

A lot of these songs have been/are/will be inspirations and/or emotional encouragement with my artwork so hence why I think they deserve to be here, I won't put up stupid shit without any reason, so please don't hate on my musical tastes!";

$tpl->songlist = array(
	array(
		'title' => 'Beirut - My Wife, Lost In The Woods',
		'description' => "I think it was my brother who introduced me to Beirut. Actually it more like I heard it playing in his room once and I interrogated him about the artist. Beirut is like a super... large band. I think one of the only bands that actually use the accordion which by the way, sounds amazing. It has a very Parisian feel to it. Also, I love the guy's voice lol. I was actually going to upload my favourite song from them, _Nantes_ but I think it's popular enough already... might as well try something new :3",
		'url' => 'dat/Beirut-Mywifelostinthewoods.mp3'
	),
	array(
		'title' => 'Sigur Ros - Olsen Olsen',
		'description' => "I forget how many years I've even had this song for. I do, however, remember how it was that I came across Sigur Ros and it so happened that one day, many years ago I said to one of my many internet consorts at the time, that I was very much enjoying Graduation (Friends Forever) by Vitamin C at the time and he told me to listen to real music and voila; Sigur Ros entered my life. Now you must also partake in the sweetest form of music your ears will ever hear!",
		'url' => 'dat/SigurRos-Olsenolsen.mp3'
	),
	array(
		'title' => 'A. R. Rahman - Dreams on Fire',
		'description' => "I don't even have the words to describe AR Rahman's genius. It's a very... bollywood-western style fusion sound and it sounds amazing! At least give it a listen before you dislike it :/",
		'url' => 'dat/A.R-Rahman-Dreams-on-Fire.mp3'
	),
	array(
		'title' => 'Stars - Your Ex-Lover is Dead (Final Fantasy Remix)',
		'description' => "I'm sure the title appeals to everyone to a certain degree. It sounds macabre and grotesquely satisfying, but it's kind of an indie-emo-pop-rock kind of genre. It's actually a beautiful song and I believe this one is a... remix. Not like it's really all *that* much different from the original.",
		'url' => 'dat/Stars-Your-Ex-Lover-Is-Dead-Final-Fantasy-Remix.mp3'
	),
	array(
		'title' => 'Yoko Kanno - Yogensha',
		'description' => "Yogensha has been the source of my inspiration and awe and wonder for so long now.. Yoko Kanno is my favourite composer, hands down, above everyone else I have ever come across. Her diversity, uniqueness of the sound that she creates is so cathartic, motivating and powerful all at the same time. Ahhhh I love Yoko Kanno... so much!",
		'url' => 'dat/ARJUNA into the another world - 13 - Yogensha.mp3'
	),
	array(
		'title' => 'Volcano Choir - Still',
		'description' => "Alright. So I found this \"band\" while perusing at a records store, downtown Toronto. They're called Bon Iver (originally, Volcano Choir is like a branch) and the vocalist sounds like an _ANGEL!_ Oh my goodness, his cathartic and soothing voice makes every 8am class bearable and a lot of their songs are very \"sound based\" melodies. They use a lot of sounds to from using their own voices, to sticks, to whatever sounds good, I suppose haha God, I *_love_* Justin Vernon! :|",
		'url' => 'dat/08 Still.mp3'
	),
);

function encodeSource($string) {
	$source = utf8_decode($string);
	$ntexto = "";
	$codekey = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-";
	for ($i = 0; $i < strlen($string); $i++) {
		$ntexto .= substr("0000".base_convert(ord($string{$i}), 10, 2), -8);
	}
	$ntexto .= substr("00000", 0, 6-strlen($ntexto)%6);
	$string = "";
	for ($i = 0; $i < strlen($ntexto)-1; $i = $i + 6) {
		$string .= $codekey{intval(substr($ntexto, $i, 6), 2)};
	}
	
	return $string;
}

foreach ($tpl->songlist AS $key => $entry)
{
	$tpl->songlist[$key]['url'] = encodeSource($tpl->songlist[$key]['url']);
}

$tpl->display($tpldir . '/index.tpl.php');
