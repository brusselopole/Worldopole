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
				<th><?= $locales->RAIDS_TABLE_START ?></th>
				<th><?= $locales->RAIDS_TABLE_END ?></th>
				<th><?= $locales->RAIDS_TABLE_GYM ?></th>
				<th><?= $locales->RAIDS_TABLE_BOSS ?></th>
			</tr>
			</thead>
			<tbody id="raidsContainer">

			</tbody>
			<tfoot>
				<tr class="raidsLoader">
					<td colspan="5"><div class="loader"></div></td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>