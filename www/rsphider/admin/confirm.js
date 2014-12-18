function checkAll(theForm, cName, allNo_stat) {
	var n=theForm.elements.length;
	for (var x=0;x<n;x++){
		if (theForm.elements[x].className.indexOf(cName) !=-1){
			if (allNo_stat.checked) {
			theForm.elements[x].checked = true;
		} else {
			theForm.elements[x].checked = false;
		}
	}
	}
}

  function confirm_del_prompt(URL) {
	if (!confirm("Do you really want to delete the backup file?")) 
		return false;	  
	window.location = URL;
	}

 function confirm_rest_prompt(URL) {
	if (!confirm("Do you really want to restore the database from backup file? Current database will be lost. \nAfter confirming 'OK', please be patient. Restore with a large backup file may take a long time. . .")) 
		return false;	  
	window.location = URL;
	}
	
 function confirm_db_prompt(URL) {
	if (!confirm("Do you really want to copy the database? Destination database will be lost and overwritten. \nAfter confirming 'OK', please be patient. Copy of a large database may take some time. . .")) 
		return false;	  
	window.location = URL;
	}
    
 function confirm_del_url(URL) {
	if (!confirm("Do you really want to delete the URL backup file?")) 
		return false;	  
	window.location = URL;
	}

 function confirm_rest_url(URL) {
	if (!confirm("Do you really want to import the Site table from URL backup file? Current database will be modified.")) 
		return false;	  
	window.location = URL;
	}	

 function confirm_del_set(URL) {
	if (!confirm("Do you really want to delete the Settings backup file?")) 
		return false;	  
	window.location = URL;
	}

 function confirm_protected(URL) {
	alert("The Default-Settings backup file is non-erasable!") 
		return false;	  

	} 

 function confirm_rest_set(URL) {
	if (!confirm("Do you really want to restore the configuration from backup file?\n\nCurrent settings will be overwritten!")) 
		return false;	  
	window.location = URL;
	}	
    