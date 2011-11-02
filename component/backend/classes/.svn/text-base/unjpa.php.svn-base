<?php
/**
 * @package DocImport
 * @copyright Copyright (c)2008 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 2, or later
 * @version 1.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

/**
 * Modified UnJPA class
 *
 */
class DocimportUnjpa
{

	var $_xform_fp = false;

	var $_archive = null;

	var $offset = 0;

	/**
	 * Singleton implementation
	 *
	 * @param string $archive Name of file to open
	 * @return DocimportUnjpa The DocimportUnjpa object instance
	 */
	function & getInstance($archive = null)
	{
		static $instance;

		if(!is_object($instance))
		{
			$instance = new DocimportUnjpa($archive);
		}

		return $instance;
	}

	/**
	 * Constructor
	 *
	 * @param string $archive Name of file to open
	 * @return DocimportUnjpa
	 */
	function DocimportUnjpa( $archive )
	{
		jimport('joomla.filesystem.file');

		if(! JFile::exists($archive) )
		{
			$this->_archive = null;
			$this->_xform_fp = false;
		}
		else
		{
			$this->_archive = $archive;
			$this->_xform_fp = fopen($this->_archive, 'rb');
		}
	}

	/**
	 * Reads the header off the JPA archive
	 *
	 * @return bool Returns true if it is a valid JPA archive
	 */
	function readHeader()
	{
		// Fail for unreadable files
		if( $this->_xform_fp === false ) return false;

		// Go to the beggining of the file
		rewind( $this->_xform_fp );

		// Read the signature
		$sig = fread( $this->_xform_fp, 3 );

		if ($sig != 'JPA') return false; // Not a JoomlaPack Archive?

		// Read and parse header length
		$header_length_array = unpack( 'v', fread( $this->_xform_fp, 2 ) );
		$header_length = $header_length_array[1];

		// Read and parse the known portion of header data (14 bytes)
		$bin_data = fread($this->_xform_fp, 14);
		$header_data = unpack('Cmajor/Cminor/Vcount/Vuncsize/Vcsize', $bin_data);

		// Load any remaining header data (forward compatibility)
		$rest_length = $header_length - 19;
		if( $rest_length > 0 ) $junk = fread($this->_xform_fp, $rest_length);

		$this->offset = ftell( $this->_xform_fp );
		return true;
	}

	/**
	 * Extracts a file off the archive, starting from the file position specified
	 * in $offset. The data is returned in an in-memory array *by reference*.
	 *
	 * @param int $offset Offset to start from
	 * @return array A hash array (keys are filename, data, offset, skip and done)
	 */
	function &extract( $offset )
	{
		$false = false; // Used to return false values in case an error occurs

		// Generate a return array
		$retArray = array(
			"filename"			=> '',		// File name extracted
			"data"				=> '',		// File data
			"offset"			=> 0,		// Offset in ZIP file
			"skip"              => false,   // Skip this?
			"done"				=> false	// Are we done yet?
		);

		// If we can't open the file, return an error condition
		if( $this->_xform_fp === false ) return $false;

		// Go to the offset specified
		if(!fseek( $this->_xform_fp, $offset ) == 0) return $false;

		// Get and decode Entity Description Block
		$signature = fread($this->_xform_fp, 3);

		// Check signature
		if( $signature == 'JPF' )
		{
			// This a JPA Entity Block. Process the header.
				
			// Read length of EDB and of the Entity Path Data
			$length_array = unpack('vblocksize/vpathsize', fread($this->_xform_fp, 4));
			// Read the path data
			$file = fread( $this->_xform_fp, $length_array['pathsize'] );
			// Read and parse the known data portion
			$bin_data = fread( $this->_xform_fp, 14 );
			$header_data = unpack('Ctype/Ccompression/Vcompsize/Vuncompsize/Vperms', $bin_data);
			// Read any unknwon data
			$restBytes = $length_array['blocksize'] - (21 + $length_array['pathsize']);
			if( $restBytes > 0 ) $junk = fread($this->_xform_fp, $restBytes);
				
			$compressionType = $header_data['compression'];
				
			// Populate the return array
			$retArray['filename'] = $file;
			$retArray['skip'] = ( $header_data['compsize'] == 0 ); // Skip over directories

			switch( $header_data['type'] )
			{
				case 0:
					// directory
					break;
						
				case 1:
					// file
					switch( $compressionType )
					{
						case 0: // No compression
							if( $header_data['compsize'] > 0 ) // 0 byte files do not have data to be read
							{
								$retArray['data'] = fread( $this->_xform_fp, $header_data['compsize'] );
							}
							break;
								
						case 1: // GZip compression
							$zipData = fread( $this->_xform_fp, $header_data['compsize'] );
							$retArray['data'] = gzinflate( $zipData );
							break;
								
						case 2: // BZip2 compression
							$zipData = fread( $this->_xform_fp, $header_data['compsize'] );
							$retArray['data'] = bzdecompress( $zipData );
							break;
					}
					break;
			}
			$retArray['offset'] = ftell( $this->_xform_fp );
			$this->offset = $retArray['offset'];
			return $retArray;
		} else {
			// This is not a file header. This means we are done.
			$retArray['done'] = true;
			return $retArray;
		}
	}
}
?>