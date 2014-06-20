<?
require_once("../../inc/ingame/game.php");

    $fbconfig['appid' ]  = FACEBOOK_APP_ID;
    $fbconfig['api'   ]  = FACEBOOK_API;
    $fbconfig['secret']  = FACEBOOK_SECRET;
 
    // Create our Application instance.
    $facebook = new Facebook(array(
      'appId'  => FACEBOOK_APP_ID,
      'secret' => FACEBOOK_SECRET,
      'cookie' => true,
    ));
	
		/*$status = $facebook->api('/'.FACEBOOK_APP_ID.'/feed', 'post',
			array(
			'message' => utf8_encode("{Hallo du daöoä}"),
			//'picture' => "{$picture}",
			'link' => "http://google.de",
			'name' => "Ich!"
		));*/
		
    // We may or may not have this data based on a $_GET or $_COOKIE based session.
    // If we get a session here, it means we found a correctly signed session using
    // the Application Secret only Facebook and the Application know. We dont know
    // if it is still valid until we make an API call using the session. A session
    // can become invalid if it has already expired (should not be getting the
    // session back in this case) or if the user logged out of Facebook.
    $session = $facebook->getSession();
 
    $fbme = null;
    // Session based graph API call.
    if ($session) {
      try {
        $uid = $facebook->getUser();
        $fbme = $facebook->api('/me');
      } catch (FacebookApiException $e) {
          d($e);
      }
    }
 
    function d($d){
        echo '<pre>';
        print_r($d);
        echo '</pre>';
    }
	
    $config['baseurl']  =   "http://syndicates-online.de/php/fb_test.php";
 
    //if user is logged in and session is valid.
    if ($fbme){
        //Retriving movies those are user like using graph api
        try{
            $movies = $facebook->api('/me/movies');
        }
        catch(Exception $o){
            d($o);
        }
 
        //Calling users.getinfo legacy api call example
        try{
            $param  =   array(
                'method'  => 'users.getinfo',
                'uids'    => $fbme['id'],
                'fields'  => 'name,current_location,profile_url',
                'callback'=> ''
            );
            $userInfo   =   $facebook->api($param);
        }
        catch(Exception $o){
            d($o);
        }
 
        //update user's status using graph api
        if (isset($_POST['tt'])){
            try {
                $statusUpdate = $facebook->api('/me/feed', 'post', array('message'=> $_POST['tt'], 'cb' => ''));
            } catch (FacebookApiException $e) {
                d($e);
            }
        }
 
        //fql query example using legacy method call and passing parameter
        try{
            //get user id
            $uid    = $facebook->getUser();
            //or you can use $uid = $fbme['id'];
 
            $fql    =   "select name, hometown_location, sex, pic_square from user where uid=" . $uid;
            $param  =   array(
                'method'    => 'fql.query',
                'query'     => $fql,
                'callback'  => ''
            );
            $fqlResult   =   $facebook->api($param);
        }
        catch(Exception $o){
            d($o);
        }
    }
	// login or logout url will be needed depending on current user state.
	if ($fbme) {
	  $logoutUrl = $facebook->getLogoutUrl();
	} else {
	  $loginUrl = $facebook->getLoginUrl();
	  //$loginUrl = $facebook->getLoginUrl(array('req_perms' => 'email,read_stream'));
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"/>
        <title>PHP SDK & Graph API base FBConnect Tutorial | Thinkdiff.net</title>
    </head>
<body>
    <div id="fb-root"></div>
        <script type="text/javascript">
            window.fbAsyncInit = function() {
                FB.init({appId: '<?=$fbconfig['appid' ]?>', status: true, cookie: true, xfbml: true});
 
                /* All the events registered */
                FB.Event.subscribe('auth.login', function(response) {
                    // do something with response
                    login();
                });
                FB.Event.subscribe('auth.logout', function(response) {
                    // do something with response
                    logout();
                });
            };
            (function() {
                var e = document.createElement('script');
                e.type = 'text/javascript';
                e.src = document.location.protocol + '//connect.facebook.net/de_DE/all.js';
                e.async = true;
                document.getElementById('fb-root').appendChild(e);
            }());
 
            function login(){
                document.location.href = "<?=$config['baseurl']?>";
            }
            function logout(){
                document.location.href = "<?=$config['baseurl']?>";
            }
</script>
<style type="text/css">
    .box{
        margin: 5px;
        border: 1px solid #60729b;
        padding: 5px;
        width: 500px;
        height: 200px;
        overflow:auto;
        background-color: #e6ebf8;
    }
</style>
    <h3>PHP SDK & Graph API base FBConnect Tutorial | Thinkdiff.net</h3>
    <?php if (!$fbme) { ?>
        You've to login using FB Login Button to see api calling result.
    <?php } ?>
    <p>
        <fb:login-button autologoutlink="true" perms="email,user_birthday,status_update,publish_stream"></fb:login-button><br />
    </p>
 
    <!-- all time check if user session is valid or not -->
    <?php if ($fbme){ ?>
    <table border="0" cellspacing="3" cellpadding="3">
        <tr>
            <td>
                <!-- Data retrived from user profile are shown here -->
                <div class="box">
                    <b>User Information using Graph API</b>
                    <?php d($fbme); ?>
                </div>
            </td>
            <td>
                <div class="box">
                    <b>User likes these movies | using graph api</b>
                     <?php d($movies); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="box">
                    <b>User Information by Calling Legacy API method "users.getinfo"</b>
                    <?php d($userInfo); ?>
                </div>
            </td>
            <td>
                <div class="box">
                    <b>FQL Query Example by calling Legacy API method "fql.query"</b>
                    <?php d($fqlResult); ?>
                </div>
            </td>
        </tr>
    </table>
    <div class="box">
        <form name="" action="<?=$config['baseurl']?>" method="post">
            <label for="tt">Status update using Graph API</label>
            <br />
            <textarea id="tt" name="tt" cols="50" rows="5">Write your status here and click 'submit'</textarea>
            <br />
            <input type="submit" value="Update My Status" />
        </form>
        <?php if (isset($statusUpdate)) { ?>
            <br />
            <b style="color: red">Status Updated Successfully! Status id is <?=$statusUpdate['id']?></b>
         <?php } ?>
    </div>
    <?php } ?>
 
    </body>
</html>