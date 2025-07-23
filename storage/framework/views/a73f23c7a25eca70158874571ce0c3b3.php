<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title inertia><?php echo e(config('app.name')); ?></title>
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css?family=Assistant|Nunito&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Assistant|Nunito&display=swap" media="print" onload="this.media='all'" />
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Assistant|Nunito&display=swap" />
    </noscript>
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <?php echo app('Illuminate\Foundation\Vite')('resources/ts/app.ts'); ?>
    <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->head; } ?>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>

<body class="bg-primary-50 bg-opacity-5">
    <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->body; } else { ?><div id="app" data-page="<?php echo e(json_encode($page)); ?>"></div><?php } ?>
</body>

</html><?php /**PATH C:\Users\hmitt\Downloads\submission_ijmems_for_pmsl\submission_ijmems_for_pmsl\resources\views/app.blade.php ENDPATH**/ ?>