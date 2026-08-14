<?php
$file = "resources/views/layouts/app.blade.php";
$content = file_get_contents($file);

// 2.
$content = str_replace(
    "<div class=\"nav-section-title mt-3\">Activity Capture Desk</div>\n                    @if(\$user->isSuperAdmin() || \$user->hasPermission(\"activities.create\") || in_array(\$user->role, [\"college_admin\", \"user\"]))",
    "@php \$sec2Active = request()->routeIs(\"submissions.*\"); @endphp\n                    <div class=\"nav-section-title mt-3 d-flex justify-content-between align-items-center\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseSec2\" aria-expanded=\"{{ \$sec2Active ? \"true\" : \"false\" }}\" style=\"cursor: pointer;\">\n                        <span>Activity Capture Desk</span>\n                        <i class=\"bi bi-chevron-down nav-accordion-icon transition-transform\"></i>\n                    </div>\n                    <div class=\"collapse {{ \$sec2Active ? \"show\" : \"\" }}\" id=\"collapseSec2\">\n                    @if(\$user->isSuperAdmin() || \$user->hasPermission(\"activities.create\") || in_array(\$user->role, [\"college_admin\", \"user\"]))",
    $content
);

$content = str_replace(
    "                    @endif\n                @endif\n\n                <!-- 3. APPROVAL AUTHORITY & VERIFICATION MODULE -->",
    "                    @endif\n                    </div>\n                @endif\n\n                <!-- 3. APPROVAL AUTHORITY & VERIFICATION MODULE -->",
    $content
);

// 3.
$content = str_replace(
    "<div class=\"nav-section-title mt-3\">Approval Authority Desk</div>\n                    <a href=\"{{ route(\"approvals.pending\") }}\"",
    "@php \$sec3Active = request()->routeIs(\"approvals.*\"); @endphp\n                    <div class=\"nav-section-title mt-3 d-flex justify-content-between align-items-center\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseSec3\" aria-expanded=\"{{ \$sec3Active ? \"true\" : \"false\" }}\" style=\"cursor: pointer;\">\n                        <span>Approval Authority Desk</span>\n                        <i class=\"bi bi-chevron-down nav-accordion-icon transition-transform\"></i>\n                    </div>\n                    <div class=\"collapse {{ \$sec3Active ? \"show\" : \"\" }}\" id=\"collapseSec3\">\n                    <a href=\"{{ route(\"approvals.pending\") }}\"",
    $content
);

$content = str_replace(
    "                    </a>\n                @endif\n\n                <!-- 4. MEDIA BROADCASTING DESK MODULE -->",
    "                    </a>\n                    </div>\n                @endif\n\n                <!-- 4. MEDIA BROADCASTING DESK MODULE -->",
    $content
);

// 4.
$content = str_replace(
    "<div class=\"nav-section-title mt-3\">Media Studio & Broadcast</div>\n                    <a href=\"{{ route(\"media.dashboard\") }}\"",
    "@php \$sec4Active = request()->routeIs(\"media.*\"); @endphp\n                    <div class=\"nav-section-title mt-3 d-flex justify-content-between align-items-center\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseSec4\" aria-expanded=\"{{ \$sec4Active ? \"true\" : \"false\" }}\" style=\"cursor: pointer;\">\n                        <span>Media Studio & Broadcast</span>\n                        <i class=\"bi bi-chevron-down nav-accordion-icon transition-transform\"></i>\n                    </div>\n                    <div class=\"collapse {{ \$sec4Active ? \"show\" : \"\" }}\" id=\"collapseSec4\">\n                    <a href=\"{{ route(\"media.dashboard\") }}\"",
    $content
);

$content = str_replace(
    "                    </a>\n                @endif\n\n                <!-- 5. ANALYTICS & REPORTS MODULE -->",
    "                    </a>\n                    </div>\n                @endif\n\n                <!-- 5. ANALYTICS & REPORTS MODULE -->",
    $content
);

// 5.
$content = str_replace(
    "<div class=\"nav-section-title mt-3\">Analytics & Intelligence</div>\n                    <a href=\"{{ route(\"analytics.index\") }}\"",
    "@php \$sec5Active = request()->routeIs(\"analytics.*\"); @endphp\n                    <div class=\"nav-section-title mt-3 d-flex justify-content-between align-items-center\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseSec5\" aria-expanded=\"{{ \$sec5Active ? \"true\" : \"false\" }}\" style=\"cursor: pointer;\">\n                        <span>Analytics & Intelligence</span>\n                        <i class=\"bi bi-chevron-down nav-accordion-icon transition-transform\"></i>\n                    </div>\n                    <div class=\"collapse {{ \$sec5Active ? \"show\" : \"\" }}\" id=\"collapseSec5\">\n                    <a href=\"{{ route(\"analytics.index\") }}\"",
    $content
);

$content = str_replace(
    "                    </a>\n                @endif\n\n                <!-- 6. INSTITUTIONAL ADMINISTRATION MODULE -->",
    "                    </a>\n                    </div>\n                @endif\n\n                <!-- 6. INSTITUTIONAL ADMINISTRATION MODULE -->",
    $content
);

// 6.
$content = str_replace(
    "<div class=\"nav-section-title mt-3\">Administration Desk</div>\n                    @if(\$user->isSuperAdmin() || \$user->hasPermission(\"colleges.view\"))",
    "@php \$sec6Active = request()->routeIs(\"superadmin.colleges\") || request()->routeIs(\"admin.colleges\") || request()->routeIs(\"superadmin.categories\") || request()->routeIs(\"superadmin.users\") || request()->routeIs(\"admin.users\"); @endphp\n                    <div class=\"nav-section-title mt-3 d-flex justify-content-between align-items-center\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseSec6\" aria-expanded=\"{{ \$sec6Active ? \"true\" : \"false\" }}\" style=\"cursor: pointer;\">\n                        <span>Administration Desk</span>\n                        <i class=\"bi bi-chevron-down nav-accordion-icon transition-transform\"></i>\n                    </div>\n                    <div class=\"collapse {{ \$sec6Active ? \"show\" : \"\" }}\" id=\"collapseSec6\">\n                    @if(\$user->isSuperAdmin() || \$user->hasPermission(\"colleges.view\"))",
    $content
);

$content = str_replace(
    "                    @endif\n                @endif\n\n                <!-- 7. SUPER ADMIN & GOVERNANCE CONTROL PANEL -->",
    "                    @endif\n                    </div>\n                @endif\n\n                <!-- 7. SUPER ADMIN & GOVERNANCE CONTROL PANEL -->",
    $content
);

// 7.
$content = str_replace(
    "<div class=\"nav-section-title text-warning mt-3\"><i class=\"bi bi-shield-lock-fill me-1\"></i> Control Panel</div>\n                    @if(\$user->isSuperAdmin() || \$user->hasPermission(\"roles.view\"))",
    "@php \$sec7Active = request()->routeIs(\"superadmin.*\") && !request()->routeIs(\"superadmin.colleges\") && !request()->routeIs(\"superadmin.categories\") && !request()->routeIs(\"superadmin.users\"); @endphp\n                    <div class=\"nav-section-title text-warning mt-3 d-flex justify-content-between align-items-center\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseSec7\" aria-expanded=\"{{ \$sec7Active ? \"true\" : \"false\" }}\" style=\"cursor: pointer;\">\n                        <span><i class=\"bi bi-shield-lock-fill me-1\"></i> Control Panel</span>\n                        <i class=\"bi bi-chevron-down nav-accordion-icon transition-transform\"></i>\n                    </div>\n                    <div class=\"collapse {{ \$sec7Active ? \"show\" : \"\" }}\" id=\"collapseSec7\">\n                    @if(\$user->isSuperAdmin() || \$user->hasPermission(\"roles.view\"))",
    $content
);

$content = str_replace(
    "                    @endif\n                @endif\n            @endauth\n        </div>",
    "                    @endif\n                    </div>\n                @endif\n            @endauth\n        </div>",
    $content
);

// CSS
$content = str_replace(
    "        .portal-card { border-radius: var(--radius-md) !important; border-color: var(--portal-border) !important; }\n    </style>",
    "        .portal-card { border-radius: var(--radius-md) !important; border-color: var(--portal-border) !important; }\n        .nav-accordion-icon { transition: transform 0.3s ease; font-size: 0.85rem; }\n        .nav-section-title[aria-expanded=\"true\"] .nav-accordion-icon { transform: rotate(180deg); }\n    </style>",
    $content
);

file_put_contents($file, $content);
echo "Done";

