

<a href="?controller=backend&action=analyse" class="btn btn-primary">Refresh</a>
<?php
	//echo \Devworx\Utility\DebugUtility::var_dump($performance);

	$fileGroups = array_reduce(
		$index,
		function($acc,$file)use($currentFile){
			$base = basename($file);
			$tokens = explode('.',$base);
			$group = array_shift($tokens);
			array_pop($tokens);
			$acc[$group][] = [
				'label' => empty($tokens) ? $group : ucfirst( implode('.',$tokens) ),
				'value' => $base
			];
			return $acc;
		},
		[]
	);
	
	$folderList = array_keys($files);
	$folderShorts = \Devworx\Utility\ArrayUtility::removeCommon( $folderList );
	foreach( $folderList as $i => $folder ){
		$short = $folderShorts[$i];
		
		$list = $files[$folder];
		unset($files[$folder]);
		$files[$short] = $list;
	}
	
	$sizeInfo = array_reduce(
		$files,
		function($listAcc,$list){
			$info = array_reduce(
				$list,
				function($fileAcc,$file){
					$fileAcc['size'] += $file['size'];
					$fileAcc['amount']++;
					return $fileAcc;
				},
				['size'=>0,'amount'=>0]
			);
			$listAcc['size'] += $info['size'];
			$listAcc['amount'] += $info['amount'];
			return $listAcc;
		},
		['size'=>0,'amount'=>0]
	);
?>

<div class="d-flex flex-row flex-wrap">
	<div class="col-3 p-2">
		<h3>System</h3>
		<ul class="list-group">
			<li class="list-group-item">
			CPU<br>
			<?php forEach( $server['cpu'] as $chip ): ?>
				<span class="badge px-2 text-bg-success"><?= $chip['Manufacturer'] ?></span>
				<?= $chip['Name'] ?> 
				<?= $chip['NumberOfCores'] ?> Core 
				<?= $chip['DataWidth'] ?>Bit 
				@<?= $chip['CurrentClockSpeed'] ?>MHz 
				Socket <?= $chip['SocketDesignation'] ?><br> 
			<?php endForEach; ?>
			</li>
			<li class="list-group-item">
			RAM<br>
			<?php forEach( $server['mem'] as $chip ): ?>
				<?= $chip['MemoryDevices'] ?>x 
				<?= $chip['Caption'] ?> 
				<?= round( $chip['MaxCapacity'] / 1024 / 1024, 2 ) ?>MB<br>
			<?php endForEach; ?>
			<?php forEach( $server['ram'] as $chip ): ?>
				<span class="badge px-2 text-bg-success"><?= $chip['Manufacturer'] ?></span>
				<?= $chip['PartNumber'] ?> 
				<?= round( $chip['Capacity'] / 1024 / 1024 / 1024, 2) ?>GB 
				<?= $chip['DataWidth'] ?>Bit 
				@<?= $chip['Speed'] ?>MHz<br>
			<?php endForEach; ?>
			</li>
			<li class="list-group-item">
			GPU<br>
			<?php forEach( $server['gpu'] as $chip ): ?>
				<span class="badge px-2 text-bg-success"><?= $chip['AdapterCompatibility'] ?></span> 
				<?= $chip['Name'] ?><br>
			<?php endForEach; ?>
			</li>
		</ul>
		<h3>Performance</h3>
		<ol class="list-group">
		<?php forEach($performance['data'] as $method => $info): ?>
			<li class="list-group-item d-flex flex-column">
				<div class="d-flex flex-row align-items-center">
					<div class="d-flex flex-row col-10">
						<span class="me-2 mi text-dark">bolt</span>
						<span><?= $method ?></span>
					</div>
					<small class="d-flex col-2"><?= round($info['total'] * 1000,4) ?>ms</small>
				</div>
				<?php if( count($info['list']) > 1 ): ?>
				<ol class="p-0">
				<?php forEach($info['list'] as $i => $stamp): ?>
					<li class="d-flex flex-row">
						<span class="d-flex col-10"><?= date("i:s",intval($stamp[0])) ?> - <?= isset($stamp[1]) ? date("i:s",intval($stamp[1])) : 'Never' ?></span>
						<small class="d-flex col-2"><?= round($info['spans'][$i] * 1000,4) ?>ms</small>
					</li>
				<?php endForEach; ?>
				</ol>
				<?php endIf; ?>
			</li>
		<?php endForEach; ?>
		</ol>
	</div>
	<div class="col-8 p-2 d-flex flex-column">
		<h2>Files</h2>
		<form method="GET" class="d-flex flex-column p-2 text-bg-light">
			<div>
				<input type="hidden" name="controller" value="backend">
				<input type="hidden" name="action" value="files">
			</div>
			<div class="d-flex flex-row">
				<select name="file">
				<?php 
					forEach($fileGroups as $group => $fileList){
						$options = [];
						forEach($fileList as $file){
							$option = ['value'=>$file['value']];
							if( $file['value'] === $currentFile )
								$option['selected'] = null;
							$options[] = \Devworx\Html::option($option,$file['label']);
						}
						echo \Devworx\Html::optgroup(['label'=>$group],$options);
					}
				?>
				</select>
				<button type="submit" class="btn btn-primary ms-2">Show</button>
			</div>
		</form>
	
		<table class="w-100">
		<thead class="text-bg-primary">
			<tr>
				<th>Name</th>
				<th>Type</th>
				<th>Size</th>
				<th>Create</th>
				<th>Modify</th>
				<!-- <th>Access</th> -->
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="5"><?= round( $sizeInfo['size'] / 1024 / 1024,2 ) ?>MB in <?= $sizeInfo['amount'] ?> Files</td>
			</tr>
		<?php forEach( $files as $folder => $list ): ?>
			<?php
				$folderSize = array_reduce(
					$list,
					fn($acc,$file)=>($acc + $file['size']),
					0
				);
			?>
			<tr class="text-bg-primary">
				<td valign="middle" colspan="2">
					<div class="d-flex flex-row align-items-center">
						<span class="me-2 mi text-white">folder</span> <span><?= $folder ?></span>
					</div>
				</td>
				<td><?= round($folderSize / 1024,2) ?> KB</td>
				<td colspan="3"><?= count($list) ?> Files ~ <?= round( $folderSize / count($list) / 1024, 2 ) ?> KB</td>
			</tr>
			<?php forEach( $list as $file ): ?>
			<tr class="text-bg-light">
				<td>
					<div class="d-flex flex-row align-items-center">
						<span class="mx-2 mi text-dark">article</span> <span><?= $file['base'] ?></span>
					</div>
				</td>
				<td><?= $file['mime'] ?></td>
				<td><?= round( $file['size'] / 1024, 2 ) ?> KB</td>
				<td><?= date('d.m.Y H:i',$file['create']) ?></td>
				<td><?= date('d.m.Y H:i',$file['modify']) ?></td>
				<!-- <td><?= date('d.m.Y H:i',$file['access']) ?></td> -->
			</tr>
			<?php endForEach; ?>
		<?php endForEach; ?>
		</tbody>
	</table>
	</div>
</div>