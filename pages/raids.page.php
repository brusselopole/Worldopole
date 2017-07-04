<header id="single-header">
<div class="row">
	<div class="col-md-12 text-center">
		<h1>
			<?= $locales->RAIDS_TITLE ?>
		</h1>
	</div>
</div>
</header>

<div class="row">
	<div class="col-md-12">
		<table class="table" id="raidsTable">
			<thead>
			<tr>
				<th><?= $locales->RAIDS_TABLE_LEVEL ?></th>
				<th style="min-width:100px""><?= $locales->RAIDS_TABLE_TIME ?></th>
				<th><?= $locales->RAIDS_TABLE_REMAINING ?></th>
				<th><?= $locales->RAIDS_TABLE_GYM ?></th>
				<th colspan="2"><?= $locales->RAIDS_TABLE_BOSS ?></th>
			</tr>
			</thead>
			<tbody id="raidsContainer">

			</tbody>
			<tfoot>
				<tr class="loadMore text-center">
					<td colspan="6"><button id="loadMoreButton" class="btn btn-default hidden"><?= $locales->RAIDS_LOAD_MORE ?></button></td>
				</tr>
				<tr class="raidsLoader">
					<td colspan="6"><div class="loader"></div></td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>