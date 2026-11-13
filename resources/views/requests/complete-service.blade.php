@extends("layouts.app")

@section("title", "Penyelesaian Jasa")
@section("page-title", "Penyelesaian Jasa: " . $assetRequest->request_id)

@section("content")
  <div class="data-table-container">
    <div class="table-header">
      <h3 class="table-title">
        ✅ Penyelesaian Jasa: {{ $assetRequest->request_id }}
      </h3>
      <a href="{{ route("requests.index") }}" class="btn btn-secondary">
        ← Kembali
      </a>
    </div>

    <div style="padding: 2rem">
      {{-- Info Pengajuan --}}
      <div
        style="
          background: #eff6ff;
          border: 1px solid #bfdbfe;
          border-radius: 8px;
          padding: 1rem 1.5rem;
          margin-bottom: 1.5rem;
        "
      >
        <p style="margin: 0; font-size: 0.9rem">
          <strong>Pengaju:</strong>
          {{ $assetRequest->requester->name }} &nbsp;·&nbsp;
          <strong>Kategori:</strong>
          {{ \App\Models\ServiceRequestItem::CATEGORIES[$assetRequest->service_category] ?? $assetRequest->service_category }}
          &nbsp;·&nbsp;
          <strong>Total Item:</strong>
          {{ $assetRequest->serviceItems->count() }} jasa
        </p>
      </div>

      <form
        action="{{ route("requests.complete-service", $assetRequest) }}"
        method="POST"
        enctype="multipart/form-data"
      >
        @csrf

        <div class="form-group" style="max-width: 400px; margin-bottom: 1.5rem">
          <label>
            Tanggal Penyelesaian
            <span style="color: red">*</span>
          </label>
          <input
            type="date"
            name="completion_date"
            class="form-control"
            value="{{ old("completion_date", date("Y-m-d")) }}"
            required
          />
        </div>

        <hr style="margin: 1.5rem 0" />

        @foreach ($assetRequest->serviceItems as $item)
          <div
            style="
              border: 1px solid var(--border-color);
              border-radius: 8px;
              padding: 1.5rem;
              margin-bottom: 1.5rem;
            "
          >
            <h4 style="margin: 0 0 0.25rem">
              🛠️ {{ $item->item_name }} ({{ $item->quantity }}
              {{ $item->unit }})
            </h4>
            @if ($item->specification)
              <p style="color: var(--text-secondary); margin: 0.25rem 0">
                <small>{{ $item->specification }}</small>
              </p>
            @endif

            @php
              $sd = $item->service_data ?? [];
            @endphp

            @if (! empty($sd))
              <div
                style="
                  background: var(--light-bg);
                  border-radius: 6px;
                  padding: 0.75rem 1rem;
                  margin: 0.75rem 0;
                  font-size: 0.85rem;
                "
              >
                @if (! empty($sd["asset_id"]))
                  @php
                    $asset = \App\Models\Asset::find($sd["asset_id"]);
                  @endphp

                  <div>
                    <strong>Aset:</strong>
                    {{ $asset->asset_id ?? "-" }} — {{ $asset->name ?? "-" }}
                  </div>
                @endif

                @if (! empty($sd["maintenance_type"]))
                  <div>
                    <strong>Jenis Pemeliharaan:</strong>
                    {{ $sd["maintenance_type"] }}
                  </div>
                @endif

                @if (! empty($sd["location"]))
                  <div>
                    <strong>Lokasi:</strong>
                    {{ $sd["location"] }}
                  </div>
                @endif

                @if (! empty($sd["start_date"]) && ! empty($sd["end_date"]))
                  <div>
                    <strong>Waktu Rencana:</strong>
                    {{ $sd["start_date"] }} s/d {{ $sd["end_date"] }}
                  </div>
                @endif
              </div>
            @endif

            <div class="form-row">
              <div class="form-group">
                <label>
                  Pelaksana / Vendor
                  <span style="color: red">*</span>
                </label>
                <input
                  type="text"
                  name="items[{{ $item->id }}][executor]"
                  class="form-control"
                  value="{{ old("items.{$item->id}.executor", $item->executor) }}"
                  placeholder="Contoh: PT. Sejuk Jaya / Teknisi Internal"
                  required
                />
              </div>
              <div class="form-group">
                <label>
                  Biaya Aktual (Rp)
                  <small style="font-weight: normal">(opsional)</small>
                </label>
                <input
                  type="number"
                  name="items[{{ $item->id }}][actual_cost]"
                  class="form-control"
                  value="{{ old("items.{$item->id}.actual_cost", $item->actual_cost) }}"
                  min="0"
                  placeholder="Biaya real setelah dikerjakan"
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>
                  Catatan Penyelesaian
                  <small style="font-weight: normal">(opsional)</small>
                </label>
                <textarea
                  name="items[{{ $item->id }}][completion_notes]"
                  class="form-control"
                  rows="2"
                  placeholder="Contoh: Sudah diganti kompresor, dites 2 jam stabil"
                >
{{ old("items.{$item->id}.completion_notes", $item->completion_notes) }}</textarea
                >
              </div>
              <div class="form-group">
                <label>
                  BAST (Berita Acara Serah Terima)
                  <small style="font-weight: normal">
                    (opsional, PDF/gambar)
                  </small>
                </label>
                <input
                  type="file"
                  name="items[{{ $item->id }}][bast_file]"
                  class="form-control"
                  accept=".pdf,.jpg,.jpeg,.png"
                />
                @if ($item->bast_file)
                  <small style="color: var(--success-color)">
                    📎 BAST sudah ada —
                    <a href="{{ $item->bast_url }}" target="_blank">
                      lihat file
                    </a>
                    . Upload baru untuk mengganti.
                  </small>
                @endif
              </div>
            </div>
          </div>
        @endforeach

        <div class="btn-group">
          <a href="{{ route("requests.index") }}" class="btn btn-secondary">
            Batal
          </a>
          <button
            type="submit"
            class="btn btn-success"
            onclick="return confirm('Tandai semua jasa di pengajuan ini sebagai SELESAI?')"
          >
            ✅ Tandai Semua Jasa Selesai
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
