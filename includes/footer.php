        </main>
    </div><!-- /.main -->
</div><!-- /.app -->

<!-- Delete confirmation dialog (opened by JS for any form with data-confirm) -->
<dialog id="confirmDialog" class="dialog">
    <div class="dialog-icon"><i data-lucide="trash-2"></i></div>
    <h3>Delete this room?</h3>
    <p id="confirmText">This action cannot be undone.</p>
    <div class="dialog-actions">
        <button type="button" class="btn btn-ghost" id="confirmCancel">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmOk">Yes, delete</button>
    </div>
</dialog>

<script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script>
(function () {
    // 1. Draw the Lucide icons (guarded so the page still works offline).
    if (window.lucide) { lucide.createIcons(); }

    // 2. Mobile sidebar toggle.
    var sidebar  = document.getElementById('sidebar');
    var backdrop = document.getElementById('backdrop');
    var menuBtn  = document.getElementById('menuBtn');
    function setMenu(open) {
        sidebar.classList.toggle('open', open);
        backdrop.classList.toggle('show', open);
    }
    menuBtn.addEventListener('click', function () { setMenu(!sidebar.classList.contains('open')); });
    backdrop.addEventListener('click', function () { setMenu(false); });

    // 3. Flash messages fade out after a few seconds.
    document.querySelectorAll('[data-autodismiss]').forEach(function (el) {
        setTimeout(function () { el.classList.add('hide'); }, 5000);
    });

    // 4. Confirmation before any form marked data-confirm is submitted (used by Delete).
    var dialog  = document.getElementById('confirmDialog');
    var pending = null;
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            ev.preventDefault();
            if (typeof dialog.showModal !== 'function') {          // very old browsers
                if (window.confirm(form.dataset.confirm)) { form.submit(); }
                return;
            }
            pending = form;
            document.getElementById('confirmText').textContent = form.dataset.confirm;
            dialog.showModal();
        });
    });
    document.getElementById('confirmCancel').addEventListener('click', function () {
        pending = null; dialog.close();
    });
    document.getElementById('confirmOk').addEventListener('click', function () {
        dialog.close();
        if (pending) { pending.submit(); }   // form.submit() does not re-trigger the 'submit' event
    });
})();
</script>
</body>
</html>
