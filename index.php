<?php
// https://gist.github.com/fazlurr/9802071
// https://base64.guru/developers/php/examples/decode-image (OK)

// Change PHP settings
@ini_set( 'upload_max_size', '128M' );
@ini_set( 'post_max_size', '128M');
@ini_set( 'memory_limit', '512M' );

// Define
define('UPLOAD_DIR', 'images/');

$file_url = '';
$file_list = '';

if ( isset( $_POST['json-url'] ) ) {
	$file_url = $_POST['json-url'];

	$json_date = json_decode(file_get_contents($file_url),true);
	$objects = $json_date['objects'];
	foreach( $objects as $object ) {
		if ( $object['type'] == 'image' && array_key_exists( 'src', $object ) && array_key_exists( 'name', $object ) ) {
			$image_64 = $object['src'];

			// Method 1 (Small Images)
			// $image_64 = str_replace( 'data:image/png;base64,', '', $image_64 );
			// $image_64 = str_replace( 'data:image/jpeg;base64,', '', $image_64 );
			// $image_64 = str_replace( 'data:image/jpg;base64,', '', $image_64 );
			// $image_64 = str_replace( 'data:image/gif;base64,', '', $image_64 );
			// $image_64 = str_replace( 'data:image/svg;base64,', '', $image_64 );
			// $image_64 = str_replace( ' ', '+', $image_64 );
			// $data = base64_decode($image_64);
			// $file = UPLOAD_DIR . $object['name'] . '-' . $object['id'] . '.png';
			// $success = file_put_contents($file, $data);
			// print $success ? $file : 'Unable to save the file.';

			// Method 2 (Large Images)
			// Support image types: png, jpeg, jpg, gif, svg
			$image_64 = str_replace( 'data:image/png;base64,', '', $image_64 );
			$image_64 = str_replace( 'data:image/jpeg;base64,', '', $image_64 );
			$image_64 = str_replace( 'data:image/jpg;base64,', '', $image_64 );
			$image_64 = str_replace( 'data:image/gif;base64,', '', $image_64 );
			$image_64 = str_replace( 'data:image/svg;base64,', '', $image_64 );
			$image_64 = str_replace( ' ', '+', $image_64 );

			// Obtain the original content (usually binary data)
			$bin = base64_decode( $image_64 );

			// Load GD resource from binary data
			$img = imageCreateFromString( $bin );

			// Make sure that the GD library was able to load the image
			// This is important, because you should not miss corrupted or unsupported images
			if ( ! $img ) {
			  die( 'Base64 value is not a valid image' );
			}

			// Specify the location where you want to save the image
			$file = UPLOAD_DIR . $object['name'] . '-' . $object['id'] . '.png';

			// Save the GD resource as PNG in the best possible quality (no compression)
			// This will strip any metadata or invalid contents (including, the PHP backdoor)
			// To block any possible exploits, consider increasing the compression level
			imagesavealpha($img, true);
			imagepng( $img, $file, 0 );

			if ( $file ) {
				$file_list .= '<li class="file-list__item"><a class="file-list__link" href="' . $file . '" download>' . $object['name'] . '</a></li>';
			}
		}
	}
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo "Get images from JSON WC Designer Pro"; ?></title>
		<link rel="stylesheet" href="assets/style.css">
	</head>
	<body>
		<div class="haru-container">
			<div class="tool-intro"><?php echo 'Get images from JSON WC Designer Pro'; ?></div>

			<div class="tool-form">
				<form method="post"  name="change" enctype="multipart-form/data">
					<div class="tool-form__row">
						<div class="tool-form__label"><?php echo "JSON URL"; ?></div>
						<div class="tool-form__input">
							<input type="text" name="json-url" id="json-url" value="<?php echo isset( $_POST['json-url'] ) ? $_POST['json-url'] : ''; ?>" />
						</div>
					</div>
					<div class="tool-form__row">
						<div class="tool-form__label"></div>
						<div class="tool-form__input">
							<input type="submit" name="submit">
						</div>
					</div>
				</form>
			</div>

			<?php if ( isset( $_POST['submit'] ) ) : ?>
			<div class="tool-result">
				<div class="file-list">
					<h6 class="file-list__heading"><?php echo "List Images"; ?></h6>
					<ul class="file-list__list">
						<?php if ( $file_list ) : ?>
							<?php echo $file_list; ?>
						<?php else : ?>
							<li class="file-list__no-image"><?php echo "No image found!"; ?></li>
						<?php endif; ?>
					</ul>
					<div class="file-list__notice">
						<div class="file-list__notice-load"><?php echo 'Please wait the page finish loading then click on the image to download it to your computer!'; ?></div>
						<div class="file-list__notice-store"><?php echo 'All images from JSON URL will store in the <strong>images</strong> folder of this PHP script!'; ?></div>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</body>
</html>
