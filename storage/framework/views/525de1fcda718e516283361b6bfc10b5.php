<?php $__env->startSection('content'); ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">We Code Messenger</div>

                    <div class="card-body" id="app">
                        <chat-app :user="<?php echo e(auth()->user()); ?>"></chat-app>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\DIPLOM\example-app\resources\views/home.blade.php ENDPATH**/ ?>