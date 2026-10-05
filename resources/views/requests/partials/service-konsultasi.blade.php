<div class="form-row">
    <div class="form-group">
        <label>Topik Konsultasi <span style="color: red;">*</span></label>
        <input type="text" name="items[{{ $index }}][service_data][topic]" class="form-control"
            placeholder="Contoh: Audit Keamanan Jaringan" required>
    </div>
    <div class="form-group">
        <label>Durasi (hari) <span style="color: red;">*</span></label>
        <input type="number" name="items[{{ $index }}][service_data][duration_days]" class="form-control" min="1"
            required>
    </div>
</div>
<div class="form-group">
    <label>Target Output</label>
    <textarea name="items[{{ $index }}][service_data][output_target]" class="form-control" rows="2"
        placeholder="Contoh: Laporan audit + rekomendasi perbaikan"></textarea>
</div>
