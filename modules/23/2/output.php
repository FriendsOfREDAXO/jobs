<?php

$categories = FriendsOfRedaxo\Jobs\Category::getAll(rex_clang::getCurrentId(), true);

echo '<div class="col-12">';
echo '<div class="row" data-match-height>';
echo '<div class="col-12"><h1>'. \Sprog\Wildcard::get('jobs_catgories', rex_clang::getCurrentId()) .'</h1></div>';
if (0 === count($categories)) {
    echo '<div class="col-12"><p>'. \Sprog\Wildcard::get('jobs_no_categories_found', rex_clang::getCurrentId()) .'</p></div>';
}
foreach ($categories as $category) {
    echo '<div class="col-12 col-sm-6 col-md-4 col-lg-3 job-box-list">';
    echo '<a href="'. rex_escape($category->getUrl()) .'" class="job-box-list-link" title="'. rex_escape($category->name).'"><div class="job-box" data-height-watch>';
    echo '<img src="index.php?rex_media_type=jobs_joblist&amp;rex_media_file='. rawurlencode((string) $category->picture) .'" alt="'. rex_escape(strip_tags($category->name)) .'">';
    echo '<h2>'. rex_escape($category->name) .'</h2>';
    echo '</div></a>';
    echo '</div>';
}
echo '</div>';
echo '</div>';
