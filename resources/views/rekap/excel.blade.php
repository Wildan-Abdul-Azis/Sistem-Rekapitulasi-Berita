<table>
    <thead>
        <tr>
            <th colspan="7" style="text-align: center; font-size: 14pt; font-weight: bold;">Keterangan Tayang</th>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr style="background-color: #007bff; color: #ffffff; font-weight: bold;">
            <th style="border: 1px solid #000000; text-align: center; width: 50px;">NO</th>
            <th style="border: 1px solid #000000; text-align: left; width: 180px;">NAMA MEDIA</th>
            <th style="border: 1px solid #000000; text-align: center; width: 130px;">TANGGAL KEGIATAN</th>
            <th style="border: 1px solid #000000; text-align: center; width: 130px;">TANGGAL TAYANG</th>
            <th style="border: 1px solid #000000; text-align: left; width: 350px;">JUDUL BERITA</th>
            <th style="border: 1px solid #000000; text-align: left; width: 350px;">LINK BERITA</th>
            <th style="border: 1px solid #000000; text-align: left; width: 180px;">KETERANGAN</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rekapList as $index => $item)
            <tr>
                <td style="border: 1px solid #000000; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000000;">{{ $item->nama_media }}</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ $item->tanggal_kegiatan ? \Carbon\Carbon::parse($item->tanggal_kegiatan)->format('d M Y') : '-' }}</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ $item->tanggal_tayang ? \Carbon\Carbon::parse($item->tanggal_tayang)->format('d M Y') : '-' }}</td>
                <td style="border: 1px solid #000000;">{{ $item->judul_berita }}</td>
                <td style="border: 1px solid #000000;">{{ $item->link_berita ?? '-' }}</td>
                <td style="border: 1px solid #000000;">{{ $item->keterangan ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="border: 1px solid #000000; text-align: center; font-style: italic; color: #777777;">
                    Tidak ada data publikasi pada periode ini.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
