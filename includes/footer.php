<?php $isLoggedInFooter = is_logged_in(); ?>
<?php if ($isLoggedInFooter): ?>
            </div>
        </section>
    </div>
    <footer class="main-footer app-footer border-top-0">
        <strong>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>.</strong>
    </footer>
</div>
<?php else: ?>
    </div>
<?php endif; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
