<div class="form-row">
    <div class="form-group">
        <label>Tanggal Mulai Sewa <span style="color: red;">*</span></label>
        <input type="date" name="items[{{ $index }}][service_data][start_date]" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Tanggal Selesai Sewa <span style="color: red;">*</span></label>
        <input type="date" name="items[{{ $index }}][service_data][end_date]" class="form-control" required>
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label>Durasi <span style="color: red;">*</span></label>
        <input type="number" name="items[{{ $index }}][service_data][duration]" class="form-control" min="1" required>
    </div>
    <div class="form-group">
        <label>Satuan Durasi <span style="color: red;">*</span></label>
        <select name="items[{{ $index }}][service_data][duration_unit]" class="form-control" required>
            <option value="">-- Pilih --</option>
            <option value="Hari">Hari</option>
            <option value="Minggu">Minggu</option>
            <option value="Bulan">Bulan</option>
            <option value="Tahun">Tahun</option>
        </select>
    </div>
    <div class="form-group">
        <label>Tujuan Sewa</label>
        <input type="text" name="items[{{ $index }}][service_data][rental_purpose]" class="form-control"
            placeholder="Contoh: Kegiatan praktikum semester ganjil">
    </div>
</div>
