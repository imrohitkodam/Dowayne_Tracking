<!-- Modal -->
<div class="modal fade" id="addCSVModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
	aria-labelledby="addCSVModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addCSVModalLabel">Add Pins From CSV </h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<label for="csvFormFile" class="form-label">Upload CSV file(each line contains an address)</label>
					<input class="form-control" type="file" id="csvFormFile" accept=".csv">
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger ms-2" data-bs-dismiss="modal">Close</button>
				<button type="button" id="addPinsFromCSVSubmit" class="btn btn-success ms-2">Submit</button>
			</div>
		</div>
	</div>
</div>