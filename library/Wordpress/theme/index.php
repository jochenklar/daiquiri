<?php
/*
 *  Copyright (c) 2012, 2013 Jochen S. Klar <jklar@aip.de>,
 *                           Adrian M. Partl <apartl@aip.de>, 
 *                           AIP E-Science (www.aip.de)
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  See the NOTICE file distributed with this work for additional
 *  information regarding copyright ownership. You may obtain a copy
 *  of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */
?>

<?php get_header(); ?>

<div class="row">
    <div id="wp-content" class="span9">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <div class="post">
                    <h2><?php the_title(); ?></h2>

                    <?php the_content(); ?>

                    <?php if (comments_open(get_the_ID())): ?>
                        <p class="small">
                            <?php if (get_comments_number() > 0): ?>
                                <a href="<?php comments_link(); ?>"><?php comments_number(); ?></a>
                            <?php else: ?>
                                <?php comments_number(); ?>
                                (<a href="<?php comments_link(); ?>">write a comment</a>)
                            <?php endif ?>
                        </p>
                    <?php endif ?>
                </div>
                <?php
            endwhile;
        else:
            ?>
            <p>Sorry, no page found.</p>
        <?php endif; ?>

        <p align="center"><?php posts_nav_link(); ?></p>
    </div>
    <div id="wp-sidebar" class="span3">
        <?php get_sidebar(); ?>
    </div>
</div>   

<?php get_footer(); ?>
