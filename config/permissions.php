<?php

return [
    'roles' => [
        'Admin' => ['dashboard', 'assets-inv', 'borrowing', 'maintenance', 'users', 'requests', 'reports', 'qrCode'],
        'Sarpras' => ['dashboard', 'assets-inv', 'borrowing', 'maintenance', 'users', 'requests', 'reports', 'qrCode'],
        'Rektor' => ['dashboard', 'requests', 'reports'],
        'Kaprodi' => ['dashboard', 'requests', 'reports'],
        'Keuangan' => ['dashboard', 'requests', 'reports'],
        'PJ Pengadaan' => ['dashboard', 'requests', 'assets-inv', 'reports'],
        'Kalab' => ['dashboard', 'assets-inv', 'maintenance', 'reports', 'requests'],
        'Aslab' => ['dashboard', 'assets-inv', 'maintenance'],
        'Tim Pemeliharaan' => ['dashboard', 'maintenance', 'reports'],
        'Administrasi' => ['dashboard'],
        'Karyawan' => ['dashboard', 'assets-inv', 'borrowing', 'maintenance'], 
        'Mahasiswa' => ['dashboard', 'assets-inv', 'borrowing', 'maintenance'], 
    ],
];

