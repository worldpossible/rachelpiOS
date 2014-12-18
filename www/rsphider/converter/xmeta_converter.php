<?php

	class x_metadata{
		var $metadocument = "";

        function setDocument($path){
			$zip = new ZipArchive;
			$res = $zip->open($path);
			if ($res === TRUE) {
				$folder = "tmp";
				$zip->extractTo($folder, array("docProps/core.xml"));
				$zip->close();
                //  extract all meta tags
				$datei = $this->metadocument = file_get_contents($folder."/docProps/core.xml");
                unlink($folder."/docProps/core.xml");
				rmdir($folder."/docProps");
			}
		}
        
		//  extract all the different meta tags in docx
		function getTitle(){
			$meta_title_start = explode("</dc:title>", $this->metadocument);
			$meta_title_end = explode("<dc:title>", $meta_title_start[0]);
			return "$meta_title_end[1] ";
		}

		function getSubject(){
			$meta_subject_start = explode("</dc:subject>", $this->metadocument);
			$meta_subject_end = explode("<dc:subject>", $meta_subject_start[0]);
			return "$meta_subject_end[1] ";
		}

		function getCreator(){
			$meta_creator_start = explode("</dc:creator>", $this->metadocument);
			$meta_creator_end = explode("<dc:creator>", $meta_creator_start[0]);
			return "$meta_creator_end[1] ";
		}

		function getKeywords(){
			$meta_keywords_start = explode("</cp:keywords>", $this->metadocument);
			$meta_keywords_end = explode("<cp:keywords>", $meta_keywords_start[0]);
			return "$meta_keywords_end[1] ";
		}

		function getDescription(){
			$meta_description_start = explode("</dc:description>", $this->metadocument);
			$meta_description_end = explode("<dc:description>", $meta_description_start[0]);
			return "$meta_description_end[1] ";
		}

		function getLastModifiedBy(){
			$meta_lastmodifiedby_start = explode("</cp:lastModifiedBy>", $this->metadocument);
			$meta_lastmodifiedby_end = explode("<cp:lastModifiedBy>", $meta_lastmodifiedby_start[0]);
            return "$meta_lastmodifiedby_end[1] ";
		}

		function getRevision(){
			$meta_revision_start = explode("</cp:revision>", $this->metadocument);
			$meta_revision_end = explode("<cp:revision>", $meta_revision_start[0]);
			return "$meta_revision_end[1] ";
		}

		function getDateCreated(){
			$meta_datecreated_start = explode("</dcterms:created>", $this->metadocument);
			$meta_datecreated_end = explode("<dcterms:created xsi:type=\"dcterms:W3CDTF\">", $meta_datecreated_start[0]);
			return "$meta_datecreated_end[1] ";
		}

		function getDateModified(){
			$meta_datemodified_start = explode("</dcterms:modified>", $this->metadocument);
			$meta_datemodified_end = explode("<dcterms:modified xsi:type=\"dcterms:W3CDTF\">", $meta_datemodified_start[0]);
			return "$meta_datemodified_end[1] ";
		}
    }

    //  convert the content of the docx
    function docx2text($filename) {
        return readZippedXML($filename, "word/document.xml");
    }

    function readZippedXML($archiveFile, $dataFile) {
        // create new ZIP archive
        $zip = new ZipArchive;
        // open received archive file
        if (true === $zip->open($archiveFile)) {
            // if done, search for the data file in the archive
            if (($index = $zip->locateName($dataFile)) !== false) {
                // if found, read it to the string
                $data = $zip->getFromIndex($index);
                $zip->close();
                // load XML from a string and skip errors and warnings
                $xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                return strip_tags($xml->saveXML());
            }
        } else {
            $zip->close();
            return "";  // in case of failure return empty string
        }
    }

?>