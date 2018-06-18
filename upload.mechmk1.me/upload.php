<?php
function getFileName($dir, $filename)
{

	if(!file_exists($dir . $filename))
		return $filename;

	$path_parts = pathinfo($dir . $filename);

	$ext = $path_parts['extension'];
	$fn = $path_parts['filename'];

	for ($i = 1; $i < 1337; $i++)
	{
		if(!empty($ext))
		{
			$newname = sprintf('%s (%u).%s', $fn, $i, $ext);
		}
		else
		{
			$newname = sprintf('%s (%u)', $fn, $i);
		}

		if(!file_exists($dir . $newname))
			return $newname;
	}

	throw new RuntimeException('No suitable file location found');
}


header('Content-type:application/json;charset=utf-8');


try
{
	//If this is not a POST request, we throw an error and quit
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		throw new RuntimeException('Not a POST request.');
	}

	//PHP thinks everything is an error. This means if everything is OK, 'error' is set to UPLOAD_ERR_OK.
	//What the fuck, PHP. Was 'error' == NULL not good enough for you?
	//Anyways, if 'error' doesn't exist or is an array(?) we quit.
	if (!isset($_FILES['file']['error']) || is_array($_FILES['file']['error']))
	{
		throw new RuntimeException('Invalid parameters.');
	}

	//Switch based on the error message
	switch ($_FILES['file']['error'])
	{
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_NO_FILE:
			throw new RuntimeException('No file sent.');
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			throw new RuntimeException('Exceeded filesize limit.');
		default:
			throw new RuntimeException('Unknown errors.');
	}

	//Set the filepath to /var/www/upload and the format to uniqid()+filename
	$dir = '/var/www/filebox.mechmk1.me';
	//$filename = sprintf('%s+%s', uniqid(), $_FILES['file']['name']);

	$filename = getFileName($dir, $_FILES['file']['name']);


	$filepath = $dir . $filename;

	//Try to save the uploaded files. If it succeeds, yay. If not, throw an exception
	if (!move_uploaded_file($_FILES['file']['tmp_name'], $filepath))
	{
		throw new RuntimeException('Failed to move uploaded file.');
	}

	// All good, send the response
	echo json_encode([
		'status' => 'ok',
		'path' => $filename
	]);

}
catch (RuntimeException $e) {
	// Something went wrong, send the err message as JSON
	http_response_code(400);

	echo json_encode([
		'status' => 'error',
		'message' => $e->getMessage()
	]);
}
