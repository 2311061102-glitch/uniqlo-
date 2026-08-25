<ul class="password-rules" data-password-rules>
    <li data-rule="length"><span class="password-rule__icon" aria-hidden="true">✓</span> Từ 8 đến 20 ký tự</li>
    <li data-rule="letters-numbers"><span class="password-rule__icon" aria-hidden="true">✓</span> Có chữ hoa, chữ thường và số</li>
    <li data-rule="special"><span class="password-rule__icon" aria-hidden="true">✓</span> Có thể dùng ký tự đặc biệt: !&quot;#$%&amp;'()*+,-./:;&lt;=&gt;?@[\]^_`{|}~</li>
</ul>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-password-rules]').forEach(function (rules) {
                const passwordInput = rules.closest('.form-group')?.querySelector('input[type="password"]');

                if (!passwordInput || passwordInput.type !== 'password') {
                    return;
                }

                const updateRules = function () {
                    const password = passwordInput.value;
                    const checks = {
                        length: password.length >= 8 && password.length <= 20,
                        'letters-numbers': /[a-z]/.test(password) && /[A-Z]/.test(password) && /[0-9]/.test(password),
                        special: true,
                    };

                    Object.entries(checks).forEach(function ([rule, passed]) {
                        rules.querySelector(`[data-rule="${rule}"]`).classList.toggle('password-rule--valid', passed);
                    });
                };

                passwordInput.addEventListener('input', updateRules);
                updateRules();
            });
        </script>
    @endpush
@endonce
