<div class="max-w-7xl mx-auto py-4">
    <div class="relative h-20 mb-6 overflow-hidden rounded-xl shadow-sm">
        <img src="<?= escape_html(url("/assets/img/banniere_hopital.png")) ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-950/15 via-transparent to-slate-900/10"></div>
        <div class="relative z-10 flex h-full items-center px-5 sm:px-6">
            <div class="min-w-0 text-white drop-shadow-sm">
                <h1 class="text-xl sm:text-2xl font-bold leading-tight">Tableau de bord</h1>
                <p class="text-xs sm:text-sm leading-relaxed">Vue d'ensemble de l'activité de l'hôpital</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <a href="<?= escape_html(url("/patients/ajouter.php")) ?>" class="inline-flex items-center gap-1 rounded-lg bg-white/20 px-2.5 py-1 text-xs font-medium text-white hover:bg-white/30 transition">+ Patient</a>
                    <a href="<?= escape_html(url("/consultations/ajouter.php")) ?>" class="inline-flex items-center gap-1 rounded-lg bg-white/20 px-2.5 py-1 text-xs font-medium text-white hover:bg-white/30 transition">+ Consultation</a>
                    <a href="<?= escape_html(url("/patients/rechercher.php?liste=1")) ?>" class="inline-flex items-center gap-1 rounded-lg bg-white/20 px-2.5 py-1 text-xs font-medium text-white hover:bg-white/30 transition">Rechercher</a>
                </div>
            </div>
            <div class="absolute left-[54%] hidden -translate-x-1/2 items-center gap-2 rounded-xl border border-white/30 bg-white/15 px-3 py-2 text-white shadow-sm backdrop-blur-sm sm:flex">
                <i data-lucide="calendar-days" class="h-4 w-4 shrink-0"></i>
                <div class="flex flex-col">
                    <span class="text-xs leading-tight">Aujourd'hui</span>
                    <span class="text-xs font-semibold leading-tight"><?= escape_html($dateAujourdhui) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <a href="<?= escape_html(url("/patients/rechercher.php?liste=1")) ?>" class="relative isolate flex min-h-[88px] items-center gap-2.5 overflow-hidden rounded-xl border border-emerald-100 bg-gradient-to-br from-white via-emerald-50/80 to-emerald-100/70 p-3 shadow-sm hover:shadow-md transition">
            <div class="relative z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100/90 text-emerald-600"><i data-lucide="users" class="h-6 w-6"></i></div>
            <div class="relative z-10 min-w-0 flex-1 pr-9">
                <h3 class="text-2xl font-bold leading-none text-slate-900"><?= $nbPatients ?></h3>
                <p class="mt-1 text-xs leading-tight text-slate-600">Patients enregistrés</p>
            </div>
            <span class="absolute right-2 top-2 z-10 rounded-full border border-emerald-200/80 bg-white/70 px-1.5 py-0.5 text-[11px] font-semibold text-emerald-700">Voir</span>
        </a>
        <a href="<?= escape_html(url("/consultations/ajouter.php")) ?>" class="relative isolate flex min-h-[88px] items-center gap-2.5 overflow-hidden rounded-xl border border-blue-100 bg-gradient-to-br from-white via-blue-50/80 to-blue-100/70 p-3 shadow-sm hover:shadow-md transition">
            <div class="relative z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-100/90 text-blue-600"><i data-lucide="stethoscope" class="h-6 w-6"></i></div>
            <div class="relative z-10 min-w-0 flex-1 pr-9">
                <h3 class="text-2xl font-bold leading-none text-slate-900"><?= $nbConsultations ?></h3>
                <p class="mt-1 text-xs leading-tight text-slate-600">Consultations</p>
            </div>
            <span class="absolute right-2 top-2 z-10 rounded-full border border-blue-200/80 bg-white/70 px-1.5 py-0.5 text-[11px] font-semibold text-blue-700">Ajouter</span>
        </a>
        <a href="<?= escape_html(url("/prescriptions/liste.php")) ?>" class="relative isolate flex min-h-[88px] items-center gap-2.5 overflow-hidden rounded-xl border border-purple-100 bg-gradient-to-br from-white via-purple-50/80 to-purple-100/70 p-3 shadow-sm hover:shadow-md transition">
            <div class="relative z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-100/90 text-purple-600"><i data-lucide="pill" class="h-6 w-6"></i></div>
            <div class="relative z-10 min-w-0 flex-1 pr-9">
                <h3 class="text-2xl font-bold leading-none text-slate-900"><?= $nbPrescriptions ?></h3>
                <p class="mt-1 text-xs leading-tight text-slate-600">Prescriptions</p>
            </div>
            <span class="absolute right-2 top-2 z-10 rounded-full border border-purple-200/80 bg-white/70 px-1.5 py-0.5 text-[11px] font-semibold text-purple-700">Voir</span>
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 mb-4">
        <div class="min-w-0 xl:col-span-6 bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"><i data-lucide="activity" class="h-4 w-4"></i></span>
                    Activité des 14 derniers jours
                </h2>
                <div class="flex items-center gap-3 text-xs text-slate-600">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Consultations</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-blue-500"></span>Prescriptions</span>
                </div>
            </div>
            <?php if ($activityTotal > 0): ?>
            <div class="h-56 w-full">
                <canvas id="activityChart" role="img" aria-label="Consultations et prescriptions par jour sur les 14 derniers jours" aria-describedby="activityChartHelp"></canvas>
            </div>
            <p id="activityChartHelp" class="mt-2 text-xs text-slate-500">Survolez un point pour afficher la date et le nombre d'actes.</p>
            <?php else: ?>
            <div class="flex h-56 flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 text-center">
                <i data-lucide="chart-no-axes-column-increasing" class="h-8 w-8 text-slate-400"></i>
                <p class="mt-2 text-sm font-medium text-slate-700">Aucune activité sur cette période</p>
                <p class="mt-1 text-xs text-slate-500">Les consultations et prescriptions apparaîtront ici.</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="min-w-0 xl:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <h2 class="text-base font-semibold text-slate-900 mb-4 flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"><i data-lucide="users" class="h-4 w-4"></i></span>
                Patients par sexe
            </h2>
            <div class="flex items-center justify-between gap-2">
                <div class="relative h-28 w-28 shrink-0">
                    <canvas id="sexeChart" class="h-full w-full"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="text-2xl font-bold leading-none text-slate-900"><?= $nbPatients ?></span>
                        <span class="mt-1 text-[10px] text-slate-500">Patients</span>
                    </div>
                </div>
                <div class="min-w-0 flex-1 space-y-3">
                    <?php foreach ($sexeLabels as $i => $label): ?>
                    <div class="flex items-start gap-1.5 text-[11px] leading-tight">
                        <span class="mt-0.5 h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: <?= escape_html($sexeColors[$i]) ?>"></span>
                        <span class="min-w-0 text-slate-700">
                            <span class="block truncate"><?= escape_html($label) ?></span>
                            <span class="text-slate-500"><?= $sexeData[$i] ?> (<?= $sexeTotal > 0 ? round(($sexeData[$i] / $sexeTotal) * 100) : 0 ?>%)</span>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="min-w-0 xl:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <h2 class="text-base font-semibold text-slate-900 mb-3 flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"><i data-lucide="stethoscope" class="h-4 w-4"></i></span>
                Dernières consultations
            </h2>
            <div>
                <div class="mb-2 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-slate-900">Dernières consultations</h3>
                    <a href="<?= escape_html(url("/patients/rechercher.php?liste=1")) ?>" class="shrink-0 text-xs font-medium text-emerald-600 hover:underline">Dossiers patients →</a>
                </div>
                <div class="space-y-1.5">
                    <?php if (count($lastConsults) > 0):
                        foreach ($lastConsults as $c): ?>
                    <div class="flex min-w-0 items-center gap-2 rounded-lg border border-slate-100 px-2 py-1.5">
                        <span class="shrink-0 text-[11px] font-semibold text-slate-800">#<?= (int) $c["no_Consultation"] ?></span>
                        <span class="shrink-0 text-[11px] text-slate-600"><?= format_date_fr($c["Date_Consultation"]) ?></span>
                        <a href="<?= escape_html(url("/patients/rechercher.php?code=" . (int) $c["Id_Patient"])) ?>" class="min-w-0 flex-1 truncate text-sm text-slate-800 hover:text-emerald-600 hover:underline"><?= escape_html($c["Nom"] . " " . $c["Prenom"]) ?></a>
                        <a href="<?= escape_html(url("/prescriptions/liste.php?no=" . (int) $c["no_Consultation"])) ?>" title="Traitement" class="shrink-0 text-emerald-600 hover:text-emerald-700"><i data-lucide="pill" class="h-3.5 w-3.5"></i></a>
                        <a href="<?= escape_html(url("/consultations/modifier.php?no=" . (int) $c["no_Consultation"])) ?>" title="Consultation" class="shrink-0 text-slate-500 hover:text-emerald-600"><i data-lucide="pencil" class="h-3.5 w-3.5"></i></a>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="py-2 text-xs text-slate-500">Aucune consultation enregistrée.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
    <div class="min-w-0 xl:col-span-5 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-4 gap-2">
            <h2 class="text-lg font-semibold text-slate-900 flex items-center gap-2"><i data-lucide="calendar-check" class="w-4 h-4 text-emerald-600"></i> Consultations d'aujourd'hui</h2>
            <a href="<?= escape_html(url("/consultations/ajouter.php")) ?>" class="shrink-0 text-xs font-medium text-emerald-600 hover:underline">+ Nouvelle consultation</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border-collapse text-sm">
                <colgroup><col style="width:8%"><col style="width:22%"><col style="width:22%"><col style="width:30%"><col style="width:18%"></colgroup>
                <thead>
                    <tr class="text-left text-slate-500 border-b border-slate-200">
                        <th class="pb-3 pr-1 font-medium">#</th>
                        <th class="pb-3 px-1 font-medium">Patient</th>
                        <th class="pb-3 px-1 font-medium">Médecin</th>
                        <th class="pb-3 px-1 font-medium">Diagnostic</th>
                        <th class="pb-3 pl-1 font-medium text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (count($todayConsults) > 0):
                        foreach ($todayConsults as $t): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 pr-1 font-medium"><a href="<?= escape_html(url("/consultations/modifier.php?no=" . (int) $t["no_Consultation"])) ?>" class="block truncate text-slate-900 hover:text-emerald-600 hover:underline" title="Consultation #<?= (int) $t["no_Consultation"] ?>">#<?= (int) $t["no_Consultation"] ?></a></td>
                        <td class="min-w-0 px-1 py-3"><a href="<?= escape_html(url("/patients/rechercher.php?code=" . (int) $t["Id_Patient"])) ?>" class="block truncate text-slate-900 hover:text-emerald-600 hover:underline" title="<?= escape_html($t["Nom"] . " " . $t["Prenom"]) ?>"><?= escape_html($t["Nom"] . " " . $t["Prenom"]) ?></a></td>
                        <td class="min-w-0 px-1 py-3 text-slate-600"><span class="block truncate" title="<?= escape_html($t["Nom_Prenom_medecin"] ?: "—") ?>"><?= escape_html($t["Nom_Prenom_medecin"] ?: "—") ?></span></td>
                        <td class="min-w-0 px-1 py-3 text-slate-600"><span class="block truncate" title="<?= escape_html($t["Diagnostique"] ?: "—") ?>"><?= escape_html(shorten_text($t["Diagnostique"] ?? "", 45)) ?></span></td>
                        <td class="py-3 pl-1 text-right text-xs">
                            <a href="<?= escape_html(url("/prescriptions/liste.php?no=" . (int) $t["no_Consultation"])) ?>" aria-label="Voir le traitement de la consultation #<?= (int) $t["no_Consultation"] ?>" title="Voir le traitement" class="inline-flex items-center justify-center gap-1 rounded-md bg-emerald-50 px-1.5 py-1 font-medium text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <i data-lucide="pill" class="h-3.5 w-3.5 shrink-0"></i><span class="hidden sm:inline">Voir</span>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="py-8 text-center text-slate-500 text-sm">Aucune consultation prévue aujourd'hui.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="min-w-0 xl:col-span-4 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h2 class="flex items-center gap-2 text-lg font-semibold text-slate-900"><i data-lucide="activity" class="h-4 w-4 text-emerald-600"></i> Activités récentes</h2>
            <span class="text-xs text-slate-500">3 dernières actions</span>
        </div>
        <div class="space-y-3 relative pl-4">
            <div class="absolute left-0 top-2 bottom-2 w-0.5 bg-slate-200 rounded-full"></div>
            <?php if (count($activities) > 0):
                foreach ($activities as $a): ?>
            <div class="relative flex min-w-0 gap-3 py-2">
                <div class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full <?= $a["action"]["dot"] ?>"></div>
                <div class="min-w-0 flex-1">
                    <div class="flex min-w-0 items-start justify-between gap-2">
                        <p class="min-w-0 flex-1 break-words text-sm font-medium text-slate-900"><?= escape_html($a["text"]) ?></p>
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold <?= $a["action"]["style"] ?>">
                            <i data-lucide="<?= escape_html($a["action"]["icon"]) ?>" class="h-3 w-3"></i>
                            <?= escape_html($a["action"]["label"]) ?>
                        </span>
                    </div>
                    <p class="mt-1 flex flex-wrap items-center gap-x-2 text-xs text-slate-600">
                        <span>Par <?= escape_html($a["username"]) ?></span>
                        <?php if ($a["datetime"] !== ""): ?><time datetime="<?= escape_html($a["datetime"]) ?>"><?= escape_html($a["time"]) ?></time><?php else: ?><span><?= escape_html($a["time"]) ?></span><?php endif; ?>
                    </p>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="text-center py-8 text-slate-500 text-sm">Aucune activité récente.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="relative isolate min-h-[180px] min-w-0 overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-50 via-teal-100 to-emerald-300 p-4 shadow-sm xl:col-span-3">
        <i aria-hidden="true" data-lucide="plus" class="absolute right-3 top-3 h-5 w-5 text-emerald-500/40"></i>
        <div class="relative z-10 max-w-[65%] pt-2">
            <h3 class="text-sm font-bold leading-tight text-slate-900">Accès rapide</h3>
            <p class="mt-1 text-xs leading-relaxed text-slate-600">Accès rapide aux dossiers patients.</p>
            <a href="<?= escape_html(url("/patients/rechercher.php?liste=1")) ?>" class="mt-3 inline-flex max-w-full items-center justify-center gap-1 rounded-full bg-emerald-700 px-2.5 py-2 text-center text-xs font-semibold leading-tight text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                <i data-lucide="folder-search" class="h-3.5 w-3.5 shrink-0"></i>
                Consulter les dossiers
                <i data-lucide="arrow-right" class="h-3 w-3 shrink-0"></i>
            </a>
        </div>
        <div class="absolute inset-y-0 right-0 flex w-[35%] items-end justify-center">
            <?php if ($medecinImage !== null): ?>
            <img src="<?= escape_html($medecinImage) ?>" alt="" class="relative z-10 h-full max-h-[230px] w-auto max-w-full object-contain object-bottom">
            <?php else: ?>
            <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-full border border-white/60 bg-white/40 text-emerald-700/50">
                <i data-lucide="stethoscope" class="h-9 w-9"></i>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</div>
