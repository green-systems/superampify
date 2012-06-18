<?php
/**
 * Superampify
 *
 * @author eskerda
 * @copyright 2012 Interstel Com.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
?>
<?php $data = $data['response']['searchResult2']; ?>
<searchResult2>
<?php if (isset($data['artist'])): ?>
<?php foreach($data['artist'] as $artist): ?>
<artist name="<?php echo $artist['name']; ?>" id="<?php echo $artist['id']; ?>"/>
<?php endforeach; ?>
<?php endif; ?>
<?php if (isset($data['album'])): ?>
<?php foreach($data['album'] as $album): ?>
<album <?php foreach($album as $key=>$value): ?>
<?php if (is_bool($value)){
		if ($value == true)
			$value = "true";
		else
			$value = "false";
		}else{
			$value = htmlentities($value);
		} 
?>
<?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?> />
<?php endforeach; ?>
<?php endif; ?>
<?php if (isset($data['song'])): ?>
<?php foreach($data['song'] as $song): ?>
<song <?php foreach($song as $key=>$value): ?>
<?php if (is_bool($value)){
		if ($value == true)
			$value = "true";
		else
			$value = "false";
		}else{
			$value = htmlentities($value);
		} 
?>
<?php echo $key; ?>="<?php echo $value; ?>" <?php endforeach; ?> />
<?php endforeach; ?>
<?php endif; ?>
</searchResult2>
