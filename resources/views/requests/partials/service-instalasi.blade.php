<div class="form-row">
    <div class="form-group">
        <label>Lokasi Instalasi <span style="color: red;">*</span></label>
        <input type="text" name="items[{{ $index }}][service_data][location]" class="form-control"
            placeholder="Contoh: Gedung A Lt. 3" required>
    </div>
    <div class="form-group">
        <label>Target Tanggal Selesai <span style="color: red;">*</span></label>
        <input type="date" name="items[{{ $index }}][service_data][target_date]" class="form-control" required>
    </div>
</div>
<div class="form-group">
    <label>Ruang Lingkup Instalasi</label>
    <textarea name="items[{{ $index }}][service_data][installation_scope]" class="form-control" rows="2"
        placeholder="Contoh: Pemasangan 5 unit AC + instalasi kabel"></textarea>
</div>
