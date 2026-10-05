<div class="form-row">
    <div class="form-group">
        <label>Jumlah Peserta <span style="color: red;">*</span></label>
        <input type="number" name="items[{{ $index }}][service_data][participant_count]" class="form-control" min="1"
            required>
    </div>
    <div class="form-group">
        <label>Target Sertifikasi <span style="color: red;">*</span></label>
        <input type="text" name="items[{{ $index }}][service_data][certification_target]" class="form-control"
            placeholder="Contoh: MTCNA, CCNA, AWS Certified" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label>Jadwal Mulai <span style="color: red;">*</span></label>
        <input type="date" name="items[{{ $index }}][service_data][schedule_start]" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Jadwal Selesai <span style="color: red;">*</span></label>
        <input type="date" name="items[{{ $index }}][service_data][schedule_end]" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Tempat Pelaksanaan</label>
        <input type="text" name="items[{{ $index }}][service_data][venue]" class="form-control"
            placeholder="Contoh: Aula STTI">
    </div>
</div>
