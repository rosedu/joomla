<?php
	/**
	 * @version		Autosave 0.7
	 * @author		NiGhTCrAwLeR
	 * @copyright	Powered by NiGhTCrAwLeR
	 * @license		GPL
	 */
 
	// no direct access
	defined('_JEXEC') or die('Restricted access');
	
	// import the JPlugin class
	jimport('joomla.event.plugin');
	
	/**
	* Autosave event listener
	*/
	class plgSystemAutosave extends JPlugin {
		
		// Settings
		var $asTitle = '<p align="center">Last time you were logged you had these pages open:</p>';
		var $asLink  = '<input type="checkbox" name="%s"> [%d] <a href="%s">%s</a><br>';
		var $asOpen = 'Open Selected Links';
		var $asAjax = '/joomla/plugins/system/autosave/delete.php';
		// End of Settings
		
		// vars that will be used along the program
		var $currentURL;
		var $currentUser;
		var $currentTitle;
		
		
		/**
		* Store the gathered data in the DB
		*/
		function storeAutosave() {
			// check to see if user is not a guest
			if ($this->currentUser->guest == 1)
				return ;
			
			$db =& JFactory::getDBO();
			
			// check to see if we have visited this page before
			$selectQuery = 'SELECT '.$db->nameQuote('url').' FROM '.$db->nameQuote('#__autosave').' WHERE '.$db->nameQuote('userid').' = '.$db->Quote($this->currentUser->id).' AND '.$db->nameQuote('url').' = '.$db->Quote($this->currentURL);
			$db->setQuery($selectQuery);
			if (!$result = $db->query()){
				echo $db->stderr();
				return FALSE;
			}
			// if yes, don't insert redundant data
			if ($db->getNumRows() > 0)
				return FALSE;
			
			
			// page not visited, so save the url
			$insertQquery = 'INSERT INTO '.$db->nameQuote('#__autosave').' VALUES
						(NULL, '.$db->Quote($this->currentUser->id).', 2, '.$db->Quote($this->currentURL).', '.$db->Quote($this->currentTitle).')';
			$db->setQuery($insertQquery);
			if (!$result = $db->query()){
				echo $db->stderr();
				return FALSE;
			}
		}
		
		/**
		* Display session restore popup
		* Also, get title of page
		*/
		function onAfterRender() {	
			// check to see if we have links to display
			$session =& JFactory::getSession();
			$displayLinks = $session->get('asDisplay', 0, 'autosave');
			
			if ($displayLinks) {
				$links = $session->get('asLinks', '', 'autosave');
				
				// something to know that we displayed the links
				$session->set('asDisplayed', '1', 'autosave');
				
				JResponse::appendBody("<div id='asLinks' width='300'>$links</div><script> loadAsPopup(); </script>");
				
				return ;
			}
			// get the current title
			$document	= JFactory::getDocument();
			$this->currentTitle = $document->getTitle();
			
			$this->storeAutosave();
			//echo $this->currentUser->username ." is at page $this->currentURL with the title $this->currentTitle<br>";
		}
		
		/**
		* Insert the necessary JS files
		*/
		function plugModal() {
			// load the modal plugin
			JHTML::_( 'behavior.modal');
			
			// insert our custom js
			$document =& JFactory::getDocument();
			$document->addScript("/joomla/plugins/system/autosave/autosave.js");
		}
		
		/**
		* Check to see if we have links to dsplay and take the apropriate actions
		*		- if we have, display them
		*		- if we already displayed them, delete them
		*		- if we don't have any, start getting some
		*/
		function onAfterInitialise() {
			// get the current user
 			$this->currentUser =& JFactory::getUser();
			
			$session =& JFactory::getSession();
			//$session->set('asCurrentUser', $this->currentUser->id, 'autosave');*/
			
			// check to see if user is not a guest
			if ($this->currentUser->guest == 1)
				return ;
			
			// get the current URL
			$this->currentURL = JRequest::getURI();
			
			// add the onUnLoad event
			JHTML::_( 'behavior.mootools' );
			$document =& JFactory::getDocument();
			$document->addScript("/joomla/plugins/system/autosave/ajax_fns.js");
			
			// send the user id and the current url
			$query = "uid=" . $this->currentUser->id . "&url=" . $this->currentURL;
			
			$content = 'window.addEvent("unload", function() { callAjax("'.$this->asAjax.'", "POST", "'.$query.'", "") } ); ';
			$document->addScriptDeclaration( $content );
			
			// check to see if we have links to display
			$displayLinks = $session->get('asDisplay', 0, 'autosave');
			$alreadyDisplayed = $session->get('asDisplayed', 0, 'autosave');
			
			// we have already shown the links so delete them
			if ($alreadyDisplayed) {
				// unset session variables
				$session->set('asDisplay', 0, 'autosave');
				$session->set('asDisplayed', 0, 'autosave');
				$displayLinks = 0;
				
				// delete data
				$this->onLogoutUser();
			}
			
			if ($displayLinks) {
				//$links = $session->get('asLinks', '', 'autosave');
				$this->plugModal();
				return ;
			}
		}

		
		/**
		* When the user logs out, delete all the autosave data
		*/
		function onLogoutUser($user = NULL) {
			if (!$user) // when called from onAfterInitialise
				$id = $this->currentUser->id;
			else {
				$id = $user['id'];
				return ; // for debugging: does not clear autosave data at logout
			}
			
			$db =& JFactory::getDBO();
			$deleteQuery = 'DELETE FROM '.$db->nameQuote('#__autosave').' WHERE '.$db->nameQuote('userid').' = '.$db->Quote($id);
			
			$db->setQuery($deleteQuery);
			if (!$result = $db->query()){
				echo $db->stderr();
				return FALSE;
				exit;
			}
		}
		
		
		/**
		* When the user logs in, check to see if he has any autosave data and pass it along
		*/
		function onLoginUser($user, $options) {
			
			$db =& JFactory::getDBO();
			
			// get user id
			$idQuery = 'SELECT '.$db->nameQuote('id').' FROM '.$db->nameQuote('#__users').' WHERE '.$db->nameQuote('username').' = '.$db->Quote($user['username']);
			$db->setQuery($idQuery);
			if (!$result = $db->query()){
				echo $db->stderr();
				return FALSE;
			}
			if (!$db->getNumRows()) {
				die('WTF!');
			}
			$userId = $db->loadResult();
			
			// get all the autosave data
			$selectQuery = 'SELECT * FROM '.$db->nameQuote('#__autosave').' WHERE '.$db->nameQuote('userid').' = '.$db->Quote($userId);
			$db->setQuery($selectQuery);
			if (!$result = $db->query()){
				echo $db->stderr();
				return FALSE;
			}

			// if we have any data
			if ($db->getNumRows()) {
				$links = $db->loadAssocList();
				
				$session =& JFactory::getSession();
				$session->set('asDisplay', 1, 'autosave');
				
				$r = $this->asTitle;
				
				foreach ($links as $i => $link) {
					$r .= sprintf($this->asLink,"open_$i",$i,$link['url'],$link['title']);
				}
				
				$session->set('asLinks', stripslashes($r), 'autosave');
			}
		}
	}
?>