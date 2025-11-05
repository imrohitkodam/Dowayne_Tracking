<!-- Modal -->
<div class="modal fade" id="addTextModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
	aria-labelledby="addTextModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addTextModalLabel">Add Pins From Text </h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<textarea id="addPinsTextArea" class="form-control" placeholder="Paste addresses here, one per line: example Statue of Liberty,New York, NY, USA Big Ben,Westminster, London SW1A 0AA, UK" rows="10"></textarea>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger ms-2" data-bs-dismiss="modal">Close</button>
				<button type="button" id="addPinsFromTextSubmit" class="btn btn-success ms-2">Submit</button>
			</div>
		</div>
	</div>
</div>