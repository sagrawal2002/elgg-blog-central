<?php

	/**
	 * Elgg blog individual post view
	 * 
	 * @package ElggBlog
	 * @uses $vars['entity'] Optionally, the blog post to view
	 */


  if (isset($vars['entity'])) 
  {
    $page_owner = page_owner_entity();

  
    //display comments link?
    if ($vars['entity']->comments_on == 'Off') 
    {
      $comments_on = FALSE;
    } 
    else 
    {
      $comments_on = TRUE;
    }
    if ((get_context() == "search") && ($vars['entity'] instanceof ElggObject)) 
    {
      //display the correct layout depending on gallery or list view
      if (get_input('search_viewtype') == "gallery")
      {
	//display the gallery view
	echo elgg_view("blog/gallery",$vars);
      } 
      else 
      {
	echo elgg_view("blog/listing",$vars);
      }
    } 
    else 
    {
      if ($vars['entity'] instanceof ElggObject) 
      {
	$url = $vars['entity']->getURL();
	$owner = $vars['entity']->getOwnerEntity();
	$canedit = $vars['entity']->canEdit();
      } 
      else 
      {
	$url = 'javascript:history.go(-1);';
	$owner = $vars['user'];
	$canedit = FALSE;
      }
	
      
      $current_user = $_SESSION['user'];
      $watch = get_plugin_setting('watch','blog');
        $user_id = get_loggedin_userid();
	 $user = get_entity($user_id);
	  if (($watch != 'no') && ($owner != $user))
      {
	$watching = check_entity_relationship($current_user->guid, 'blogwatcher', $owner->guid);
	  
	  if($watching != FALSE)
	  {
	    $watch1 = "(" . elgg_echo('blog:watch:delete') . ")";
	  }
	  else
	  {
	    $watch1 = "(" . elgg_echo('blog:watch:add') . ")";
	  }
// 	}
      }
      
if ((!isset($vars['full'])) || ((isset($vars['full'])) && ($vars['full'] != FALSE))){
blog_view_count($vars['entity'], $page_owner);
}
      if (get_context() != 'blog')
      {
	echo '<div class="search_listing">';
      }
  ?>
  <div class="singleview">
    <div class="blog_post">
      <h3><a href="<?php echo $url; ?>"><?php echo $vars['entity']->title; ?></a></h3>
      <!-- display the user icon -->
      <div class="blog_post_icon">
      <?php
	$content_owner = $vars['entity']->container_guid;
	if(get_plugin_setting("iconoverwrite","blog")== "yes" && !empty($content_owner))
	{
	  echo elgg_view("profile/icon",array('entity' => get_entity($content_owner), 'size' => 'tiny','entity_id'=>$vars['entity']->guid));
	}
	else
	{
	  echo elgg_view("profile/icon",array('entity' => $owner, 'size' => 'tiny','entity_id'=>$vars['entity']->guid));
	}
      ?>
      </div>
      <p class="strapline">
	<?php
	  echo sprintf(elgg_echo("blog:strapline"),date("F j, Y", $vars['entity']->time_created));
	  echo ', ' . elgg_echo('blog:created:by') . '<a href="' . $vars['url'] . 'pg/blog/owner/' . $owner->username . '">' . $owner->name . '</a>';
	  //Blog Watching
	  $watch = get_plugin_setting('watch','blog');
	  $user_id = get_loggedin_userid();
	 $user = get_entity($user_id);
	  if (($watch != 'no') && ($owner != $user) && (isloggedin()))
	  {
	    $ts = time();
	    $token = generate_action_token($ts);	
	    echo " <a href='{$vars['url']}action/blog/watch?owner_guid={$owner->guid}&__elgg_token=$token&__elgg_ts=$ts'>{$watch1}</a>";
	  }
	  $container = get_entity($vars['entity']->container_guid);
	  if ($container instanceof ElggGroup){
	  echo '</p><p class="strapline">' . elgg_echo('blog:ingroup') . ' <a href="' . $container->getURL() . '">' . $container->name . '</a>';
	  }	
	
	?>
	</p><p class="strapline">
	<?php
	  
	  $current_count = $vars['entity']->getAnnotations("blogview");
	  //$current_count = $vars['entity']->blogviews;
	  if((!$current_count)||(is_null($current_count)))
	  {
	    $current_count = 0;
	  }
	  else 
	  {
	    $current_count = $current_count[0]->value;
	  }
	  echo "  " . elgg_echo('blog:stats:blogview') . "(" . $current_count . ") ";
	// display the comments link 

	  if($comments_on && $vars['entity'] instanceof ElggObject)
	  {
	    //get the number of comments
	    $num_comments = elgg_count_comments($vars['entity']);
	?>
	<a href="#comment"><?php echo sprintf(elgg_echo("comments")) . " (" . $num_comments . ")"; ?></a>
<?php		
							}
	  //If Featuring is turned on
	  $feature = get_plugin_setting('feature','blog');
	  if ($feature != 'no')
	  {		        
	    $star = $vars['url']."mod/blog/graphics/star.png";
	    $featured_star = "<img src='$star'>";
	    if($vars['entity']->featured_blog == "yes")
	    {
	      $featured = "$featured_star";
	      echo "<div class='featured_blog_spot'>" . $featured . elgg_echo('blog:featured:yes') .  "</div>";
	    }
	  }
	  ?>
      </p>
      <!-- display tags -->
      <?php
	$tags = elgg_view('output/tags', array('tags' => $vars['entity']->tags));
	if (!empty($tags)) 
	{
	  echo '<p class="tags">' . $tags . '</p>';
	}
				  
	$categories = elgg_view('categories/view', $vars);
	if (!empty($categories)) 
	{
	  echo '<p class="categories">' . $categories . '</p>';
	}
	$icon = $vars['entity']->icon;
	echo "<div class='blog_icon'>" . $icon . "</div>";
      ?>
      <div class="clearfloat"></div>
      <?php if (get_context() == 'blog')
	{  // do not display body text if this is being displayed outside of the blog module ?> 
	  <div class="blog_post_body" style="display:block;">
			  
	  <!-- display the actual blog post -->
	  <?php
	    // see if we need to display the full post or just an excerpt
	    if ((!isset($vars['full'])) || ((isset($vars['full'])) && ($vars['full'] != FALSE)))
	    {
	      echo elgg_view('output/longtext', array('value' => $vars['entity']->description));
	    } 
	    else 
	    {
	      $body = elgg_get_excerpt($vars['entity']->description, 500);
	      // add a "read more" link if cropped.
	      if (elgg_substr($body, -3, 3) == '...') 
	      {
		$body .= " <a href=\"{$vars['entity']->getURL()}\">" . elgg_echo('blog:read_more') . '</a>';
	      }
					  
	      echo elgg_view('output/longtext', array('value' => $body));
	    }
	  ?>
	  </div>
	  <div class="clearfloat"></div>			
	  <!-- display edit options if it is the blog post owner -->
	  <?php
	  // see if we need to display the full post or just an excerpt
	  if (!isset($vars['full']) || (isset($vars['full']) && $vars['full'] != FALSE)) 
	  {
	  ?>
	  <p class="options<?php echo $vars['entity']->guid;?>">
	  <?php 
	  }
	  else
	  { ?>
	  <p class="options<?php echo $vars['entity']->guid;?>" style="display:block;">
	  <?php }
	  if ($canedit) 
	  {
	  ?>
	  <a href="<?php echo $vars['url']; ?>mod/blog/edit.php?blogpost=<?php echo $vars['entity']->getGUID(); ?>"><?php echo elgg_echo("edit"); ?></a>  &nbsp; 
	  <?php
	    echo elgg_view("output/confirmlink", array(
		  'href' => $vars['url'] . "action/blog/delete?blogpost=" . $vars['entity']->getGUID(),
		  'text' => elgg_echo('delete'),
		  'confirm' => elgg_echo('deleteconfirm'),
		));
	
		// Allow the menu to be extended
	    echo elgg_view("editmenu",array('entity' => $vars['entity']));
						
		//for admins display the feature or unfeature option
	    $feature = get_plugin_setting('feature','blog');
	    if ($feature != 'no')
	    {
	      if(isadminloggedin())
	      {
		if($vars['entity']->featured_blog == "yes")
		{
		  $featured_url = elgg_add_action_tokens_to_url($vars['url'] . "action/blog/featured?blogguid=" . $vars['entity']->guid . "&action_type=unfeature");
		  $wording = elgg_echo("blog:unfeature");
		}
		else
		{
		  $featured_url = elgg_add_action_tokens_to_url($vars['url'] . "action/blog/featured?blogguid=" . $vars['entity']->guid . "&action_type=feature");
		  $wording = elgg_echo("blog:feature");
		}
		echo "&nbsp &nbsp";
		echo "<a href='$featured_url'>$wording</a>";
	      } // end if admin logged in
	    } // end if feature = no
	  } // end if canedit
	  ?>
	  </p>
		  
		  
	  <?php
	  if ((!isset($vars['full'])) || ((isset($vars['full'])) && ($vars['full'] != FALSE))){
	    if (isset($vars['entity']->tags))
	      related_blogs($vars['entity']);
	  }
	}  // do not display body text if this is being displayed outside of the blog module 

      ?> 
    </div>
  </div>
  <?php
    if (get_context() != 'blog')
    {
      echo '</div>';
    }	
    } // end - if custom view 
  } // end - if there is a blog entity
?>
