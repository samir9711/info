<?php
// config/lessons.php
return [
    'completion_threshold_percent' => 90,
    'new_view_threshold_minutes' => 30,   // لو مرت هذه الدقائق نعتبر زيارة جديدة
    'new_view_restart_seconds' => 10,     // لو أعاد المستخدم التشغيل إلى أقل من prev - هذا نعتبره restart
];
