<?php
	// no direct access
	defined('_JEXEC') or die('Restricted access');
	
	// import the JPlugin class
	jimport('joomla.event.plugin');
	
	/**
	* Autosave event listener
	*/
	class plgSystemAutosave extends JPlugin {
		
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
		*/
		function onAfterRender() {
			// check to see if we have links to display
			$session =& JFactory::getSession();
			$displayLinks = $session->get('asDisplay', 0, 'autosave');
			
			if ($displayLinks) {
				$links = $session->get('asLinks', '', 'autosave');
				
				JResponse::appendBody("<script>displayBlackDiv('$links');</script>");
				
				return ;
			}
			// get the current title
			$document	= JFactory::getDocument();
			$this->currentTitle = $document->getTitle();
			
			$this->storeAutosave();
			//echo $this->currentUser->username ." is at page $this->currentURL with the title $this->currentTitle<br>";
		}
		
		function onAfterDispatch() {
			/*$included_files = get_included_files();
			$fd = fopen("D:/AppServ/www/joomla/test.txt","a");
			foreach ($included_files as $filename) {
				fwrite($fd, "$filename\r\n");
			}
			fwrite($fd,"------------------------\r\n\r\n");*/
			//fclose($fd);
		}
		
		/**
		* 
		*/
		function onAfterInitialise() {
			/*$session =& JFactory::getSession();
			$TC = $session->get('TC', 0, 'TC');
			$session->set('TC',$TC+1,"TC");
			
			$fd = fopen("D:/AppServ/www/joomla/test.txt","a");
			fwrite($fd,"TCi = ".$TC."\r\n");*/
			//fclose($fd);
			
			// get the current user
 			$this->currentUser =& JFactory::getUser();
			
			// check to see if user is not a guest
			if ($this->currentUser->guest == 1)
				return ;
			
			// check to see if we have links to display
			$session =& JFactory::getSession();
			$displayLinks = $session->get('asDisplay', 0, 'autosave');
			
			if ($displayLinks) {
				$links = $session->get('asLinks', '', 'autosave');
				
				$document =& JFactory::getDocument();
				$document->addScript("/joomla/plugins/system/autosave/autosave.js");
				$document->addStyleSheet("/joomla/plugins/system/autosave/autosave.css");
				
				return ;
			}
			
			// get the current URL
			$this->currentURL = JRequest::getURI();
		}

		
		/**
		* When the user logs out, delete all the autosave data
		*/
		function onLogoutUser($user) {
			/*$db =& JFactory::getDBO();
			$deleteQuery = 'DELETE FROM '.$db->nameQuote('#__autosave').' WHERE '.$db->nameQuote('userid').' = '.$db->Quote($user['id']);
			
			$db->setQuery($deleteQuery);
			if (!$result = $db->query()){
				echo $db->stderr();
				return FALSE;
				exit;
			}*/
		}
		
		
		/**
		* When the user logs in, greet him with a popup displaying all previously opened links
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
				
				$r = '<p align="center">Last time you were logged you had these pages open:</p>';
				
				foreach ($links as $i => $link) {
					$r .= "<input type=\"checkbox\" name=\"open_$i\"> [$i] <a href=\"$link[url]\">$link[title]</a><br>";
				}
				
				//$r .= '<a href="javascript:document.getElementById(\'darkenScreenObject\').style.display=none;">Close</a>';
				
				$session->set('asLinks', $r, 'autosave');
			}
		}
	}
?>