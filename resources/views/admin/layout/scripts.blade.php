    <script>
        const editorMeta = {
            content: { title: 'Content', sub: 'Control the public trust content customers see after scanning a batch QR code.' },
            about: { title: 'About', sub: 'Manage the official company identity shown on the verification page.' },
            appearance: { title: 'Appearance', sub: 'Customize the branded look of the public scan result page.' },
        };

        document.querySelectorAll('[data-editor-tab]').forEach((tab) => {
            tab.addEventListener('click', () => {
                const name = tab.dataset.editorTab;
                document.querySelectorAll('[data-editor-tab]').forEach((item) => item.classList.remove('active'));
                tab.classList.add('active');
                document.querySelectorAll('.editor-page').forEach((page) => page.classList.remove('active'));
                document.getElementById(`epage-${name}`)?.classList.add('active');
                document.getElementById('editor-page-title').textContent = editorMeta[name].title;
                document.getElementById('editor-page-subtitle').textContent = editorMeta[name].sub;
            });
        });

        const profileEditor = document.querySelector('[data-profile-editor]');
        if (profileEditor) {
            const saveState = document.querySelector('[data-editor-save-state]');
            const phoneShell = profileEditor.querySelector('.phone-shell');
            const previewCompanies = profileEditor.querySelectorAll('[data-preview-company-output]');
            const previewHeadline = profileEditor.querySelector('[data-preview-headline-output]');
            const previewStatus = profileEditor.querySelector('[data-preview-status]');
            const previewBadge = profileEditor.querySelector('[data-preview-badge]');
            const previewTitle = profileEditor.querySelector('[data-preview-title]');
            const previewMessage = profileEditor.querySelector('[data-preview-message-output]');
            const previewVerification = profileEditor.querySelector('[data-preview-verification]');
            const previewHelp = profileEditor.querySelector('[data-preview-help]');
            const previewHelpCopy = profileEditor.querySelector('[data-preview-help-copy]');
            const previewProfileContent = profileEditor.querySelector('[data-preview-profile-content]');
            const previewFooter = profileEditor.querySelector('[data-preview-footer]');
            const modeCopy = {
                genuine: { label: 'Verified Genuine', title: 'Authentic product verified', tone: 'success' },
                suspicious: { label: 'Review Needed', title: 'Extra verification recommended', tone: 'warning', help: 'This product could not be fully verified. Contact the manufacturer before use or distribution.' },
                invalid: { label: 'Invalid Code', title: 'Code not recognized', tone: 'danger', help: 'This QR code could not be verified. Contact the manufacturer or retailer if you need help.', hideProfile: true, hideFooter: true },
                expired: { label: 'Expired Batch', title: 'This batch has expired', tone: 'warning', help: 'This product is past its expiry date. Contact the manufacturer before use or distribution.' },
                recalled: { label: 'Recalled Product', title: 'Do not use this product', tone: 'danger', help: 'Stop using or distributing this product immediately. Contact the manufacturer for the next steps.', hideProfile: true, hideFooter: true },
                revoked: { label: 'Revoked Code', title: 'This QR code is no longer active', tone: 'danger', help: 'Do not rely on this QR code. Contact the manufacturer to confirm the product before use or distribution.', hideProfile: true, hideFooter: true },
            };
            let dirty = false;
            let previewMode = 'genuine';
            let draggedRow = null;
            const markDirty = () => {
                dirty = true;
                saveState?.classList.add('dirty');
                if (saveState) saveState.textContent = 'Unsaved changes';
            };
            const renumber = (type) => {
                profileEditor.querySelectorAll(`[data-editor-repeat-list="${type}"] [data-editor-repeat-row]`).forEach((row, index) => {
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/\[\d+\]/, `[${index}]`);
                    });
                });
            };
            const prepareRepeatRows = () => {
                profileEditor.querySelectorAll('[data-editor-repeat-row]').forEach((row) => {
                    row.draggable = true;
                });
            };
            profileEditor.querySelectorAll('[data-editor-repeat-add]').forEach((button) => {
                button.addEventListener('click', () => {
                    const type = button.dataset.editorRepeatAdd;
                    const list = profileEditor.querySelector(`[data-editor-repeat-list="${type}"]`);
                    const template = document.querySelector(`[data-editor-repeat-template="${type}"]`);
                    if (!list || !template) return;
                    list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', list.children.length));
                    prepareRepeatRows();
                    markDirty();
                });
            });
            profileEditor.addEventListener('dragstart', (event) => {
                draggedRow = event.target.closest('[data-editor-repeat-row]');
                if (draggedRow) event.dataTransfer.effectAllowed = 'move';
            });
            profileEditor.addEventListener('dragover', (event) => {
                const target = event.target.closest('[data-editor-repeat-row]');
                if (!draggedRow || !target || target === draggedRow || target.parentElement !== draggedRow.parentElement) return;
                event.preventDefault();
                const bounds = target.getBoundingClientRect();
                target.parentElement.insertBefore(draggedRow, event.clientY < bounds.top + bounds.height / 2 ? target : target.nextSibling);
            });
            profileEditor.addEventListener('dragend', () => {
                const list = draggedRow?.closest('[data-editor-repeat-list]');
                if (list) renumber(list.dataset.editorRepeatList);
                if (draggedRow) markDirty();
                draggedRow = null;
            });
            profileEditor.addEventListener('click', (event) => {
                const remove = event.target.closest('[data-editor-repeat-remove]');
                if (remove) {
                    const list = remove.closest('[data-editor-repeat-list]');
                    const type = list?.dataset.editorRepeatList;
                    remove.closest('[data-editor-repeat-row]')?.remove();
                    if (type) renumber(type);
                    markDirty();
                }
                const mediaRemove = event.target.closest('[data-editor-media-remove]');
                if (mediaRemove) {
                    const card = mediaRemove.closest('.card');
                    const input = card?.querySelector('[data-editor-remove-input]');
                    const preview = card?.querySelector('[data-editor-upload-preview]');
                    if (input) input.value = '1';
                    if (preview) {
                        const placeholder = Object.assign(document.createElement('span'), { textContent: 'Choose image' });
                        placeholder.dataset.editorUploadPreview = '';
                        preview.replaceWith(placeholder);
                    }
                    if (card?.querySelector('[name="profile[logo]"]')) {
                        const logo = profileEditor.querySelector('[data-preview-logo]');
                        if (logo) logo.textContent = logo.dataset.previewCompanyInitials || 'A8';
                    }
                    if (card?.querySelector('[name="profile[cover_image]"]')) {
                        const cover = profileEditor.querySelector('[data-preview-cover]');
                        if (cover) cover.style.backgroundImage = '';
                    }
                    markDirty();
                }
                if (event.target.closest('[data-editor-certificate-remove]')) {
                    const input = profileEditor.querySelector('[data-certificate-remove]');
                    if (input) input.value = '1';
                    event.target.closest('.editor-file-row')?.remove();
                    markDirty();
                }
            });
            profileEditor.querySelectorAll('[data-editor-upload-input]').forEach((input) => {
                input.addEventListener('change', () => {
                    const file = input.files?.[0];
                    const zone = input.closest('[data-editor-upload-zone]');
                    if (!file || !zone) return;
                    const image = document.createElement('img');
                    image.className = 'editor-upload-image';
                    image.dataset.editorUploadPreview = '';
                    image.src = URL.createObjectURL(file);
                    zone.querySelector('[data-editor-upload-preview]')?.replaceWith(image);
                    const remove = zone.parentElement?.querySelector('[data-editor-remove-input]');
                    if (remove) remove.value = '0';
                    if (input.name.includes('[logo]')) profileEditor.querySelector('[data-preview-logo]').innerHTML = `<img src="${image.src}" alt="">`;
                    if (input.name.includes('[cover_image]')) profileEditor.querySelector('[data-preview-cover]').style.backgroundImage = `url("${image.src}")`;
                    markDirty();
                });
            });
            profileEditor.querySelectorAll('.layout-opt, .btn-style-opt').forEach((option) => {
                option.addEventListener('click', () => {
                    option.parentElement.querySelectorAll('.selected').forEach((item) => item.classList.remove('selected'));
                    option.classList.add('selected');
                    markDirty();
                });
            });
            profileEditor.querySelectorAll('[data-editor-color]').forEach((input) => {
                input.addEventListener('input', () => {
                    input.parentElement.querySelector('b').textContent = input.value.toUpperCase();
                    if (input.dataset.editorColor === 'button_color') previewBadge.style.background = input.value;
                    markDirty();
                });
            });
            const renderPreview = () => {
                const meta = modeCopy[previewMode];
                previewCompanies.forEach((previewCompany) => previewCompany.textContent = profileEditor.querySelector('[data-preview-company]')?.value || 'Company Name');
                if (previewHeadline) previewHeadline.textContent = profileEditor.querySelector('[data-preview-headline]')?.value || 'Verify your original product';
                if (previewStatus) previewStatus.textContent = meta.label;
                if (previewBadge) previewBadge.textContent = meta.label;
                if (previewTitle) previewTitle.textContent = meta.title;
                if (previewVerification) previewVerification.dataset.tone = meta.tone;
                const message = profileEditor.querySelector(`[data-preview-message="${previewMode}"]`)?.value
                    || (previewMode === 'expired' ? 'The QR code is real, but the product batch expiry date has passed.' : previewMode === 'recalled' ? 'This product or batch has been recalled. Contact the manufacturer immediately.' : previewMode === 'revoked' ? 'This code was revoked by the manufacturer. Contact support before using this product.' : '');
                if (previewMessage) previewMessage.textContent = message;
                previewHelp?.classList.toggle('phone-public-hidden', ! meta.help);
                if (previewHelpCopy) previewHelpCopy.textContent = meta.help || '';
                previewProfileContent?.classList.toggle('phone-public-hidden', Boolean(meta.hideProfile));
                previewFooter?.classList.toggle('phone-public-hidden', Boolean(meta.hideFooter));
                profileEditor.querySelectorAll('[data-preview-promotional-media]').forEach((item) => item.classList.toggle('phone-public-hidden', previewMode !== 'genuine'));
                profileEditor.querySelectorAll('[data-preview-visibility]').forEach((item) => {
                    item.classList.toggle('phone-public-hidden', ! item.dataset.previewVisibility.split(',').includes(previewMode));
                });
            };
            profileEditor.querySelectorAll('[data-preview-mode]').forEach((button) => {
                button.addEventListener('click', () => {
                    profileEditor.querySelectorAll('[data-preview-mode]').forEach((item) => item.classList.remove('active'));
                    button.classList.add('active');
                    previewMode = button.dataset.previewMode;
                    renderPreview();
                });
            });
            profileEditor.querySelectorAll('[data-preview-device]').forEach((button) => {
                button.addEventListener('click', () => {
                    profileEditor.querySelectorAll('[data-preview-device]').forEach((item) => item.classList.remove('active'));
                    button.classList.add('active');
                    phoneShell?.classList.toggle('desktop', button.dataset.previewDevice === 'desktop');
                });
            });
            profileEditor.addEventListener('input', (event) => {
                if (!event.target.matches('[data-preview-mode], [data-preview-device]')) markDirty();
                renderPreview();
            });
            profileEditor.addEventListener('change', markDirty);
            profileEditor.addEventListener('submit', () => { dirty = false; });
            window.addEventListener('beforeunload', (event) => {
                if (!dirty) return;
                event.preventDefault();
                event.returnValue = '';
            });
            prepareRepeatRows();
            renderPreview();
        }

        document.querySelectorAll('[data-account-tab]').forEach((tab) => {
            tab.addEventListener('click', () => {
                const name = tab.dataset.accountTab;
                document.querySelectorAll('[data-account-tab]').forEach((item) => item.classList.remove('active'));
                tab.classList.add('active');
                document.querySelectorAll('.account-page').forEach((page) => page.classList.remove('active'));
                document.getElementById(`account-page-${name}`)?.classList.add('active');
            });
        });

        const userDrawer = document.getElementById('user-create-drawer');
        const userDrawerTitle = document.getElementById('user-drawer-title');
        const userDrawerSubtitle = document.getElementById('user-drawer-subtitle');
        const rolePreviewContent = {
            owner: ['Full user and settings access', 'Manage companies, products, and batches', 'Review scan analytics and operations'],
            admin: ['Manage companies and product operations', 'Create batches and QR workflows', 'Review reports and scan logs'],
            operator: ['Handle products and batches', 'Review scan logs', 'Limited settings access'],
        };
        const renderRolePreview = (preview, role) => {
            if (!preview) return;

            const permissions = rolePreviewContent[role] ?? rolePreviewContent.operator;
            preview.innerHTML = `
                <div class="role-preview-title">${role.charAt(0).toUpperCase() + role.slice(1)} permissions</div>
                <div class="role-preview-list">
                    ${permissions.map((permission) => `<span>${permission}</span>`).join('')}
                </div>
            `;
        };
        const openUserDrawer = (formKey = 'create') => {
            const targetForm = document.getElementById(`user-form-${formKey}`) ?? document.getElementById('user-form-create');

            document.querySelectorAll('.drawer-user-form').forEach((form) => form.classList.remove('active'));
            targetForm?.classList.add('active');

            if (userDrawerTitle && targetForm?.dataset.formTitle) {
                userDrawerTitle.textContent = targetForm.dataset.formTitle;
            }

            if (userDrawerSubtitle && targetForm?.dataset.formSubtitle) {
                userDrawerSubtitle.textContent = targetForm.dataset.formSubtitle;
            }

            userDrawer?.classList.add('active');
            userDrawer?.setAttribute('aria-hidden', 'false');
            targetForm?.querySelector('input[name="name"]')?.focus();
        };
        const closeUserDrawer = () => {
            userDrawer?.classList.remove('active');
            userDrawer?.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-user-drawer-open]').forEach((button) => {
            button.addEventListener('click', () => openUserDrawer(button.dataset.userForm));
        });

        document.querySelectorAll('[data-user-drawer-close]').forEach((button) => {
            button.addEventListener('click', closeUserDrawer);
        });

        document.querySelectorAll('[data-role-select]').forEach((select) => {
            const preview = select.closest('.drawer-user-form')?.querySelector('[data-role-preview]');
            renderRolePreview(preview, select.value);
            select.addEventListener('change', () => renderRolePreview(preview, select.value));
        });

        document.querySelectorAll('[data-static-role]').forEach((preview) => {
            renderRolePreview(preview, preview.dataset.staticRole);
        });

        document.querySelectorAll('.alert-success, .alert-error').forEach((toast) => {
            setTimeout(() => {
                toast.classList.add('toast-hide');
                setTimeout(() => toast.remove(), 220);
            }, 3200);
        });

        const showToast = (message, type = 'success') => {
            const toast = document.createElement('div');
            toast.className = type === 'error' ? 'alert-error' : 'alert-success';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('toast-hide');
                setTimeout(() => toast.remove(), 220);
            }, 2600);
        };

        document.querySelectorAll('[data-copy-text]').forEach((button) => {
            button.addEventListener('click', async (event) => {
                event.stopPropagation();
                const text = button.dataset.copyText ?? '';

                try {
                    await navigator.clipboard.writeText(text);
                    showToast('Verification URL copied.');
                } catch (error) {
                    showToast('Could not copy the URL.', 'error');
                }
            });
        });

        const deleteAlert = document.getElementById('delete-alert');
        const deleteAlertCopy = document.getElementById('delete-alert-copy');
        const deleteAlertInput = document.querySelector('[data-delete-input]');
        const deleteAlertConfirm = document.querySelector('[data-delete-confirm]');
        let pendingDeleteForm = null;
        let pendingDeleteMatch = '';

        const closeDeleteAlert = () => {
            deleteAlert?.classList.remove('active');
            deleteAlert?.setAttribute('aria-hidden', 'true');
            pendingDeleteForm = null;
            pendingDeleteMatch = '';
            if (deleteAlertInput) {
                deleteAlertInput.value = '';
            }
            if (deleteAlertConfirm) {
                deleteAlertConfirm.disabled = true;
            }
        };

        document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (form.dataset.deleteConfirmed === 'true') return;

                if (form.matches('[data-bulk-form]')) {
                    updateBulkInputs();

                    if (selectedUserIds().length === 0) {
                        event.preventDefault();
                        return;
                    }

                    if (form.querySelector('[name="action"]')?.value !== 'delete') {
                        return;
                    }
                }

                if (form.matches('[data-order-bulk-form]')) {
                    const selectedOrders = Array.from(document.querySelectorAll('[data-order-select]'))
                        .filter((checkbox) => checkbox.checked)
                        .map((checkbox) => checkbox.value);
                    const inputWrap = form.querySelector('[data-order-bulk-inputs]');

                    if (inputWrap) {
                        inputWrap.innerHTML = selectedOrders.map((id) => `<input type="hidden" name="order_ids[]" value="${id}">`).join('');
                    }

                    if (selectedOrders.length === 0) {
                        event.preventDefault();
                        return;
                    }

                    if (form.querySelector('[name="action"]')?.value !== 'delete') {
                        return;
                    }
                }

                if (form.matches('[data-batch-bulk-form]')) {
                    const selectedBatches = Array.from(document.querySelectorAll('[data-batch-select]'))
                        .filter((checkbox) => checkbox.checked)
                        .map((checkbox) => checkbox.value);
                    const inputWrap = form.querySelector('[data-batch-bulk-inputs]');

                    if (inputWrap) {
                        inputWrap.innerHTML = selectedBatches.map((id) => `<input type="hidden" name="batch_ids[]" value="${id}">`).join('');
                    }

                    if (selectedBatches.length === 0) {
                        event.preventDefault();
                        return;
                    }

                    if (form.querySelector('[name="action"]')?.value !== 'delete') {
                        return;
                    }
                }

                if (form.matches('[data-product-bulk-form]')) {
                    const selectedProducts = Array.from(document.querySelectorAll('[data-product-select]'))
                        .filter((checkbox) => checkbox.checked)
                        .map((checkbox) => checkbox.value);
                    const inputWrap = form.querySelector('[data-product-bulk-inputs]');

                    if (inputWrap) {
                        inputWrap.innerHTML = selectedProducts.map((id) => `<input type="hidden" name="product_ids[]" value="${id}">`).join('');
                    }

                    if (selectedProducts.length === 0) {
                        event.preventDefault();
                        return;
                    }

                    if (form.querySelector('[name="action"]')?.value !== 'delete') {
                        return;
                    }
                }

                if (form.matches('[data-category-bulk-form]')) {
                    const selectedCategories = Array.from(document.querySelectorAll('[data-category-select]'))
                        .filter((checkbox) => checkbox.checked)
                        .map((checkbox) => checkbox.value);
                    const inputWrap = form.querySelector('[data-category-bulk-inputs]');

                    if (inputWrap) {
                        inputWrap.innerHTML = selectedCategories.map((id) => `<input type="hidden" name="category_ids[]" value="${id}">`).join('');
                    }

                    if (selectedCategories.length === 0) {
                        event.preventDefault();
                        return;
                    }

                    if (form.querySelector('[name="action"]')?.value !== 'delete') {
                        return;
                    }
                }

                if (form.matches('[data-company-bulk-form]')) {
                    const selectedCompanies = Array.from(document.querySelectorAll('[data-company-select]'))
                        .filter((checkbox) => checkbox.checked)
                        .map((checkbox) => checkbox.value);
                    const inputWrap = form.querySelector('[data-company-bulk-inputs]');

                    if (inputWrap) {
                        inputWrap.innerHTML = selectedCompanies.map((id) => `<input type="hidden" name="company_ids[]" value="${id}">`).join('');
                    }

                    if (selectedCompanies.length === 0) {
                        event.preventDefault();
                        return;
                    }

                    if (form.querySelector('[name="action"]')?.value !== 'delete') {
                        return;
                    }
                }

                event.preventDefault();
                pendingDeleteForm = form;
                pendingDeleteMatch = form.dataset.deleteMatch || form.dataset.deleteName || 'DELETE';

                if (deleteAlertCopy) {
                    const name = form.dataset.deleteName || 'this user';
                    deleteAlertCopy.textContent = `This will permanently delete ${name}. Type "${pendingDeleteMatch}" to confirm.`;
                }

                deleteAlert?.classList.add('active');
                deleteAlert?.setAttribute('aria-hidden', 'false');
                deleteAlertInput?.focus();
            });
        });

        deleteAlertInput?.addEventListener('input', () => {
            if (deleteAlertConfirm) {
                deleteAlertConfirm.disabled = deleteAlertInput.value.trim().toLowerCase() !== pendingDeleteMatch.toLowerCase();
            }
        });

        document.querySelectorAll('[data-delete-cancel]').forEach((button) => {
            button.addEventListener('click', closeDeleteAlert);
        });

        deleteAlertConfirm?.addEventListener('click', () => {
            const form = pendingDeleteForm;
            pendingDeleteForm = null;
            if (form) {
                form.dataset.deleteConfirmed = 'true';
            }
            form?.requestSubmit ? form.requestSubmit() : form?.submit();
        });

        const companyDrawer = document.getElementById('company-drawer');
        const companyDrawerTitle = document.getElementById('company-drawer-title');
        const companyDrawerSubtitle = document.getElementById('company-drawer-subtitle');
        const openCompanyDrawer = (formKey = 'create') => {
            const targetForm = document.getElementById(`company-form-${formKey}`) ?? document.getElementById('company-form-create');
            document.querySelectorAll('#company-drawer .drawer-user-form').forEach((form) => form.classList.remove('active'));
            targetForm?.classList.add('active');
            if (companyDrawerTitle && targetForm?.dataset.formTitle) companyDrawerTitle.textContent = targetForm.dataset.formTitle;
            if (companyDrawerSubtitle && targetForm?.dataset.formSubtitle) companyDrawerSubtitle.textContent = targetForm.dataset.formSubtitle;
            companyDrawer?.classList.add('active');
            companyDrawer?.setAttribute('aria-hidden', 'false');
            targetForm?.querySelector('input, select, textarea')?.focus();
        };
        const closeCompanyDrawer = () => {
            companyDrawer?.classList.remove('active');
            companyDrawer?.setAttribute('aria-hidden', 'true');
        };
        document.querySelectorAll('[data-company-drawer-open]').forEach((button) => button.addEventListener('click', () => openCompanyDrawer(button.dataset.companyForm)));
        document.querySelectorAll('[data-company-drawer-close]').forEach((button) => button.addEventListener('click', closeCompanyDrawer));

        const initializeCompanyWizard = (form) => {
            const wizard = form?.querySelector('[data-company-wizard]');
            if (!wizard) return;

            const steps = Array.from(wizard.querySelectorAll('[data-wizard-step]'));
            const progressButtons = Array.from(wizard.querySelectorAll('[data-wizard-go]'));
            const back = wizard.querySelector('[data-wizard-back]');
            const next = wizard.querySelector('[data-wizard-next]');
            const draft = wizard.querySelector('[data-wizard-draft]');
            const submit = wizard.querySelector('[data-wizard-submit]');
            const status = wizard.querySelector('[data-wizard-status]');
            const companyName = wizard.querySelector('[data-wizard-company-name]');
            const reviewName = wizard.querySelector('[data-wizard-review-name]');
            const reviewInitials = wizard.querySelector('[data-wizard-review-initials]');
            const reviewCover = wizard.querySelector('[data-wizard-review-cover]');
            const socialList = wizard.querySelector('[data-wizard-social-list]');
            const socialTemplate = wizard.querySelector('[data-wizard-social-template]');
            const socialAdd = wizard.querySelector('[data-wizard-social-add]');
            let socialIndex = 0;
            let currentStep = 0;

            const validateStep = () => {
                const fields = Array.from(steps[currentStep]?.querySelectorAll('input, select, textarea') ?? []);
                const invalid = fields.find((field) => !field.checkValidity());
                invalid?.reportValidity();
                return !invalid;
            };
            const updateReview = () => {
                const name = companyName?.value.trim() || 'New company';
                const initials = name.split(/\s+/).filter(Boolean).slice(0, 2).map((word) => word[0]).join('').toUpperCase() || 'CO';
                if (reviewName) reviewName.textContent = name;
                if (reviewInitials && !reviewInitials.querySelector('img')) reviewInitials.textContent = initials;
            };
            const render = () => {
                steps.forEach((step, index) => step.classList.toggle('active', index === currentStep));
                progressButtons.forEach((button, index) => {
                    button.classList.toggle('active', index === currentStep);
                    button.classList.toggle('done', index < currentStep);
                });
                if (back) back.style.display = currentStep > 0 ? 'inline-flex' : 'none';
                if (next) next.style.display = currentStep < steps.length - 1 ? 'inline-flex' : 'none';
                if (submit) submit.style.display = currentStep === steps.length - 1 ? 'inline-flex' : 'none';
                if (currentStep === steps.length - 1) updateReview();
            };
            const goTo = (target) => {
                if (target > currentStep && !validateStep()) return;
                currentStep = Math.max(0, Math.min(target, steps.length - 1));
                render();
            };

            next?.addEventListener('click', () => goTo(currentStep + 1));
            back?.addEventListener('click', () => goTo(currentStep - 1));
            progressButtons.forEach((button) => button.addEventListener('click', () => {
                const target = Number(button.dataset.wizardGo);
                if (target <= currentStep + 1) goTo(target);
            }));
            companyName?.addEventListener('input', updateReview);
            wizard.querySelectorAll('[data-wizard-upload]').forEach((upload) => {
                const input = upload.querySelector('[data-wizard-upload-input]');
                const preview = upload.querySelector('[data-wizard-upload-preview]');
                const label = upload.querySelector('[data-wizard-upload-label]');

                input?.addEventListener('change', () => {
                    const file = input.files?.[0];
                    if (!file) return;

                    const url = URL.createObjectURL(file);
                    preview.innerHTML = `<img src="${url}" alt="">`;
                    if (label) label.textContent = file.name;

                    if (input.matches('[data-wizard-logo-input]') && reviewInitials) {
                        reviewInitials.innerHTML = `<img src="${url}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`;
                    }

                    if (input.matches('[data-wizard-cover-input]') && reviewCover) {
                        reviewCover.style.backgroundImage = `url("${url}")`;
                    }
                });
            });
            const addSocialLink = () => {
                if (!socialList || !socialTemplate || socialList.children.length >= 8) return;
                const markup = socialTemplate.innerHTML.replaceAll('__INDEX__', socialIndex++);
                socialList.insertAdjacentHTML('beforeend', markup);
            };
            socialAdd?.addEventListener('click', addSocialLink);
            socialList?.addEventListener('click', (event) => {
                event.target.closest('[data-wizard-social-remove]')?.closest('[data-wizard-social-row]')?.remove();
            });
            draft?.addEventListener('click', () => {
                if (status) status.value = 'pending';
            });
            submit?.addEventListener('click', () => {
                if (status) status.value = 'active';
            });
            form.addEventListener('submit', (event) => {
                if (event.submitter === draft) return;
                if (currentStep < steps.length - 1 || !validateStep()) {
                    event.preventDefault();
                    goTo(Math.min(currentStep + 1, steps.length - 1));
                }
            });
            render();
        };

        initializeCompanyWizard(document.getElementById('company-form-create'));

        const companyRows = Array.from(document.querySelectorAll('[data-company-row]'));
        const companySearch = document.querySelector('[data-company-search]');
        const companyStatus = document.querySelector('[data-company-status-filter]');
        const companyProfile = document.querySelector('[data-company-profile-filter]');
        const companyNoResults = document.querySelector('[data-company-no-results]');
        const companyPagination = document.querySelector('[data-company-pagination]');
        const companyPageSummary = document.querySelector('[data-company-page-summary]');
        const companyPageCurrent = document.querySelector('[data-company-page-current]');
        const companyPagePrev = document.querySelector('[data-company-page-prev]');
        const companyPageNext = document.querySelector('[data-company-page-next]');
        const companySelectAll = document.querySelector('[data-company-select-all]');
        const companyChecks = Array.from(document.querySelectorAll('[data-company-select]'));
        const companyBulkForm = document.querySelector('[data-company-bulk-form]');
        const companyBulkInputs = document.querySelector('[data-company-bulk-inputs]');
        const companyBulkCount = document.querySelector('[data-company-bulk-count]');
        const companiesPerPage = 8;
        let currentCompanyPage = 1;
        const visibleCompanyRows = () => companyRows.filter((row) => row.style.display !== 'none');
        const updateCompanySelection = () => {
            const selected = companyChecks.filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
            if (companyBulkInputs) companyBulkInputs.innerHTML = selected.map((id) => `<input type="hidden" name="company_ids[]" value="${id}">`).join('');
            companyBulkForm?.classList.toggle('active', selected.length > 0);
            if (companyBulkCount) companyBulkCount.textContent = `${selected.length} selected`;
            const visibleChecks = visibleCompanyRows().map((row) => row.querySelector('[data-company-select]')).filter(Boolean);
            const checkedVisible = visibleChecks.filter((checkbox) => checkbox.checked).length;
            if (companySelectAll) {
                companySelectAll.checked = visibleChecks.length > 0 && checkedVisible === visibleChecks.length;
                companySelectAll.indeterminate = checkedVisible > 0 && checkedVisible < visibleChecks.length;
            }
        };
        const renderCompanyTable = () => {
            const query = (companySearch?.value ?? '').trim().toLowerCase();
            const matches = companyRows.filter((row) =>
                (!query || row.dataset.companySearchText?.includes(query))
                && (!companyStatus || companyStatus.value === 'all' || row.dataset.companyStatus === companyStatus.value)
                && (!companyProfile || companyProfile.value === 'all' || row.dataset.companyProfile === companyProfile.value)
            );
            const totalPages = Math.max(1, Math.ceil(matches.length / companiesPerPage));
            currentCompanyPage = Math.min(currentCompanyPage, totalPages);
            const start = (currentCompanyPage - 1) * companiesPerPage;
            const end = start + companiesPerPage;
            companyRows.forEach((row) => {
                const index = matches.indexOf(row);
                row.style.display = index >= start && index < end ? '' : 'none';
            });
            companyNoResults?.classList.toggle('active', companyRows.length > 0 && matches.length === 0);
            if (companyPagination) companyPagination.style.display = companyRows.length > 0 ? 'flex' : 'none';
            if (companyPageSummary) companyPageSummary.textContent = `Showing ${matches.length ? start + 1 : 0}-${Math.min(end, matches.length)} of ${matches.length} companies`;
            if (companyPageCurrent) companyPageCurrent.textContent = `${currentCompanyPage} / ${totalPages}`;
            if (companyPagePrev) companyPagePrev.disabled = currentCompanyPage <= 1;
            if (companyPageNext) companyPageNext.disabled = currentCompanyPage >= totalPages;
            updateCompanySelection();
        };
        [companySearch, companyStatus, companyProfile].forEach((control) => {
            control?.addEventListener('input', () => { currentCompanyPage = 1; renderCompanyTable(); });
            control?.addEventListener('change', () => { currentCompanyPage = 1; renderCompanyTable(); });
        });
        companyPagePrev?.addEventListener('click', () => { currentCompanyPage = Math.max(1, currentCompanyPage - 1); renderCompanyTable(); });
        companyPageNext?.addEventListener('click', () => { currentCompanyPage += 1; renderCompanyTable(); });
        companyChecks.forEach((checkbox) => checkbox.addEventListener('change', updateCompanySelection));
        companySelectAll?.addEventListener('change', () => {
            visibleCompanyRows().forEach((row) => row.querySelector('[data-company-select]').checked = companySelectAll.checked);
            updateCompanySelection();
        });
        document.querySelector('[data-clear-company-filters]')?.addEventListener('click', () => {
            if (companySearch) companySearch.value = '';
            [companyStatus, companyProfile].forEach((control) => { if (control) control.value = 'all'; });
            currentCompanyPage = 1;
            renderCompanyTable();
        });
        companyRows.forEach((row) => row.addEventListener('click', (event) => {
            if (!event.target.closest('button, a, input, select, form')) openCompanyDrawer(`details-${row.dataset.companyId}`);
        }));
        renderCompanyTable();

        const userRows = Array.from(document.querySelectorAll('[data-user-row]'));
        const userSearchInput = document.querySelector('[data-user-search]');
        const userRoleFilter = document.querySelector('[data-user-role-filter]');
        const userStatusFilter = document.querySelector('[data-user-status-filter]');
        const userNoResults = document.querySelector('[data-user-no-results]');
        const userPagination = document.querySelector('[data-user-pagination]');
        const userPageSummary = document.querySelector('[data-user-page-summary]');
        const userPageCurrent = document.querySelector('[data-user-page-current]');
        const userPagePrev = document.querySelector('[data-user-page-prev]');
        const userPageNext = document.querySelector('[data-user-page-next]');
        const userSelectAll = document.querySelector('[data-user-select-all]');
        const userSelectionBoxes = Array.from(document.querySelectorAll('[data-user-select]'));
        const bulkForm = document.querySelector('[data-bulk-form]');
        const bulkInputs = document.querySelector('[data-bulk-inputs]');
        const bulkCount = document.querySelector('[data-bulk-count]');
        const clearUserFilters = document.querySelector('[data-clear-user-filters]');
        const usersPerPage = 8;
        let currentUserPage = 1;

        const selectedUserIds = () => userSelectionBoxes
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.value);

        const updateBulkInputs = () => {
            const ids = selectedUserIds();

            if (bulkInputs) {
                bulkInputs.innerHTML = ids.map((id) => `<input type="hidden" name="user_ids[]" value="${id}">`).join('');
            }

            bulkForm?.classList.toggle('active', ids.length > 0);

            if (bulkCount) {
                bulkCount.textContent = `${ids.length} selected`;
            }
        };

        const visibleUserRows = () => userRows.filter((row) => row.style.display !== 'none');

        const syncSelectAll = () => {
            if (!userSelectAll) return;

            const visibleBoxes = visibleUserRows()
                .map((row) => row.querySelector('[data-user-select]'))
                .filter(Boolean);
            const checkedCount = visibleBoxes.filter((checkbox) => checkbox.checked).length;

            userSelectAll.checked = visibleBoxes.length > 0 && checkedCount === visibleBoxes.length;
            userSelectAll.indeterminate = checkedCount > 0 && checkedCount < visibleBoxes.length;
        };

        const renderUserTable = () => {
            const query = (userSearchInput?.value ?? '').trim().toLowerCase();
            const role = userRoleFilter?.value ?? 'all';
            const status = userStatusFilter?.value ?? 'all';
            const matches = userRows.filter((row) => {
                const matchesQuery = !query || row.dataset.userSearchText?.includes(query);
                const matchesRole = role === 'all' || row.dataset.userRole === role;
                const matchesStatus = status === 'all' || row.dataset.userStatus === status;

                return matchesQuery && matchesRole && matchesStatus;
            });
            const totalPages = Math.max(1, Math.ceil(matches.length / usersPerPage));

            currentUserPage = Math.min(currentUserPage, totalPages);
            const start = (currentUserPage - 1) * usersPerPage;
            const end = start + usersPerPage;

            userRows.forEach((row) => {
                const index = matches.indexOf(row);
                row.style.display = index >= start && index < end ? '' : 'none';
            });

            if (userNoResults) {
                userNoResults.classList.toggle('active', userRows.length > 0 && matches.length === 0);
            }

            if (userPagination) {
                userPagination.style.display = userRows.length > 0 ? 'flex' : 'none';
            }

            if (userPageSummary) {
                const visibleStart = matches.length === 0 ? 0 : start + 1;
                const visibleEnd = Math.min(end, matches.length);
                userPageSummary.textContent = `Showing ${visibleStart}-${visibleEnd} of ${matches.length} users`;
            }

            if (userPageCurrent) {
                userPageCurrent.textContent = `${currentUserPage} / ${totalPages}`;
            }

            if (userPagePrev) {
                userPagePrev.disabled = currentUserPage <= 1;
            }

            if (userPageNext) {
                userPageNext.disabled = currentUserPage >= totalPages;
            }

            syncSelectAll();
            updateBulkInputs();
        };

        [userSearchInput, userRoleFilter, userStatusFilter].forEach((control) => {
            control?.addEventListener('input', () => {
                currentUserPage = 1;
                renderUserTable();
            });
            control?.addEventListener('change', () => {
                currentUserPage = 1;
                renderUserTable();
            });
        });

        userPagePrev?.addEventListener('click', () => {
            currentUserPage = Math.max(1, currentUserPage - 1);
            renderUserTable();
        });

        userPageNext?.addEventListener('click', () => {
            currentUserPage += 1;
            renderUserTable();
        });

        userRows.forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('button, a, input, select, form')) return;
                openUserDrawer(`activity-${row.dataset.userId}`);
            });
        });

        userSelectionBoxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                syncSelectAll();
                updateBulkInputs();
            });
        });

        userSelectAll?.addEventListener('change', () => {
            visibleUserRows().forEach((row) => {
                const checkbox = row.querySelector('[data-user-select]');
                if (checkbox) {
                    checkbox.checked = userSelectAll.checked;
                }
            });

            syncSelectAll();
            updateBulkInputs();
        });

        clearUserFilters?.addEventListener('click', () => {
            if (userSearchInput) userSearchInput.value = '';
            if (userRoleFilter) userRoleFilter.value = 'all';
            if (userStatusFilter) userStatusFilter.value = 'all';
            currentUserPage = 1;
            renderUserTable();
        });

        renderUserTable();

        const categoryDrawer = document.getElementById('category-drawer');
        const categoryDrawerTitle = document.getElementById('category-drawer-title');
        const categoryDrawerSubtitle = document.getElementById('category-drawer-subtitle');
        const openCategoryDrawer = (formKey = 'create') => {
            const targetForm = document.getElementById(`category-form-${formKey}`) ?? document.getElementById('category-form-create');
            document.querySelectorAll('#category-drawer .drawer-user-form').forEach((form) => form.classList.remove('active'));
            targetForm?.classList.add('active');
            if (categoryDrawerTitle && targetForm?.dataset.formTitle) categoryDrawerTitle.textContent = targetForm.dataset.formTitle;
            if (categoryDrawerSubtitle && targetForm?.dataset.formSubtitle) categoryDrawerSubtitle.textContent = targetForm.dataset.formSubtitle;
            categoryDrawer?.classList.add('active');
            categoryDrawer?.setAttribute('aria-hidden', 'false');
            targetForm?.querySelector('input, select, textarea')?.focus();
        };
        const closeCategoryDrawer = () => {
            categoryDrawer?.classList.remove('active');
            categoryDrawer?.setAttribute('aria-hidden', 'true');
        };
        document.querySelectorAll('[data-category-drawer-open]').forEach((button) => button.addEventListener('click', () => openCategoryDrawer(button.dataset.categoryForm)));
        document.querySelectorAll('[data-category-drawer-close]').forEach((button) => button.addEventListener('click', closeCategoryDrawer));

        const categoryRows = Array.from(document.querySelectorAll('[data-category-row]'));
        const categorySearch = document.querySelector('[data-category-search]');
        const categoryCompany = document.querySelector('[data-category-company-filter]');
        const categoryStatus = document.querySelector('[data-category-status-filter]');
        const categoryUse = document.querySelector('[data-category-use-filter]');
        const categoryNoResults = document.querySelector('[data-category-no-results]');
        const categoryPagination = document.querySelector('[data-category-pagination]');
        const categoryPageSummary = document.querySelector('[data-category-page-summary]');
        const categoryPageCurrent = document.querySelector('[data-category-page-current]');
        const categoryPagePrev = document.querySelector('[data-category-page-prev]');
        const categoryPageNext = document.querySelector('[data-category-page-next]');
        const categorySelectAll = document.querySelector('[data-category-select-all]');
        const categoryChecks = Array.from(document.querySelectorAll('[data-category-select]'));
        const categoryBulkForm = document.querySelector('[data-category-bulk-form]');
        const categoryBulkInputs = document.querySelector('[data-category-bulk-inputs]');
        const categoryBulkCount = document.querySelector('[data-category-bulk-count]');
        let currentCategoryPage = 1;
        const categoriesPerPage = 8;
        const visibleCategoryRows = () => categoryRows.filter((row) => row.style.display !== 'none');
        const updateCategorySelection = () => {
            const selected = categoryChecks.filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
            if (categoryBulkInputs) categoryBulkInputs.innerHTML = selected.map((id) => `<input type="hidden" name="category_ids[]" value="${id}">`).join('');
            categoryBulkForm?.classList.toggle('active', selected.length > 0);
            if (categoryBulkCount) categoryBulkCount.textContent = `${selected.length} selected`;
            const visibleChecks = visibleCategoryRows().map((row) => row.querySelector('[data-category-select]')).filter(Boolean);
            const checkedVisible = visibleChecks.filter((checkbox) => checkbox.checked).length;
            if (categorySelectAll) {
                categorySelectAll.checked = visibleChecks.length > 0 && checkedVisible === visibleChecks.length;
                categorySelectAll.indeterminate = checkedVisible > 0 && checkedVisible < visibleChecks.length;
            }
        };
        const renderCategoryTable = () => {
            const query = (categorySearch?.value ?? '').trim().toLowerCase();
            const matches = categoryRows.filter((row) =>
                (!query || row.dataset.categorySearchText?.includes(query))
                && (!categoryCompany || categoryCompany.value === 'all' || row.dataset.categoryCompany === categoryCompany.value)
                && (!categoryStatus || categoryStatus.value === 'all' || row.dataset.categoryStatus === categoryStatus.value)
                && (!categoryUse || categoryUse.value === 'all' || row.dataset.categoryUse === categoryUse.value)
            );
            const totalPages = Math.max(1, Math.ceil(matches.length / categoriesPerPage));
            currentCategoryPage = Math.min(currentCategoryPage, totalPages);
            const start = (currentCategoryPage - 1) * categoriesPerPage;
            const end = start + categoriesPerPage;
            categoryRows.forEach((row) => row.style.display = matches.indexOf(row) >= start && matches.indexOf(row) < end ? '' : 'none');
            categoryNoResults?.classList.toggle('active', categoryRows.length > 0 && matches.length === 0);
            if (categoryPagination) categoryPagination.style.display = categoryRows.length > 0 ? 'flex' : 'none';
            if (categoryPageSummary) categoryPageSummary.textContent = `Showing ${matches.length ? start + 1 : 0}-${Math.min(end, matches.length)} of ${matches.length} categories`;
            if (categoryPageCurrent) categoryPageCurrent.textContent = `${currentCategoryPage} / ${totalPages}`;
            if (categoryPagePrev) categoryPagePrev.disabled = currentCategoryPage <= 1;
            if (categoryPageNext) categoryPageNext.disabled = currentCategoryPage >= totalPages;
            updateCategorySelection();
        };
        [categorySearch, categoryCompany, categoryStatus, categoryUse].forEach((control) => {
            control?.addEventListener('input', () => { currentCategoryPage = 1; renderCategoryTable(); });
            control?.addEventListener('change', () => { currentCategoryPage = 1; renderCategoryTable(); });
        });
        categoryPagePrev?.addEventListener('click', () => { currentCategoryPage = Math.max(1, currentCategoryPage - 1); renderCategoryTable(); });
        categoryPageNext?.addEventListener('click', () => { currentCategoryPage += 1; renderCategoryTable(); });
        categoryChecks.forEach((checkbox) => checkbox.addEventListener('change', updateCategorySelection));
        categorySelectAll?.addEventListener('change', () => { visibleCategoryRows().forEach((row) => row.querySelector('[data-category-select]').checked = categorySelectAll.checked); updateCategorySelection(); });
        document.querySelector('[data-clear-category-filters]')?.addEventListener('click', () => {
            if (categorySearch) categorySearch.value = '';
            [categoryCompany, categoryStatus, categoryUse].forEach((control) => { if (control) control.value = 'all'; });
            currentCategoryPage = 1;
            renderCategoryTable();
        });
        categoryRows.forEach((row) => row.addEventListener('click', (event) => {
            if (!event.target.closest('button, a, input, select, form')) openCategoryDrawer(`edit-${row.dataset.categoryId}`);
        }));
        renderCategoryTable();

        const productDrawer = document.getElementById('product-drawer');
        const productDrawerTitle = document.getElementById('product-drawer-title');
        const productDrawerSubtitle = document.getElementById('product-drawer-subtitle');
        const initializeProductWizard = (form) => {
            const wizard = form.querySelector('[data-product-wizard]');
            if (!wizard) return;

            const steps = Array.from(wizard.querySelectorAll('[data-product-wizard-step]'));
            const progressButtons = Array.from(wizard.querySelectorAll('[data-product-wizard-go]'));
            const back = wizard.querySelector('[data-product-wizard-back]');
            const next = wizard.querySelector('[data-product-wizard-next]');
            const submit = wizard.querySelector('[data-product-wizard-submit]');
            const companyInput = wizard.querySelector('[data-company-value]');
            const imageInput = wizard.querySelector('[data-product-upload-input]');
            const imagePreview = wizard.querySelector('[data-product-upload-preview]');
            const imageStatus = wizard.querySelector('[data-product-image-status]');
            const removeImage = wizard.querySelector('[data-product-remove-image]');
            const videoUrl = wizard.querySelector('[data-product-video-url]');
            const videoStatus = wizard.querySelector('[data-product-video-status]');
            const reviewVideo = wizard.querySelector('[data-product-review-video]');
            const galleryInput = wizard.querySelector('[data-product-gallery-input]');
            const galleryList = wizard.querySelector('[data-product-gallery-list]');
            const galleryStatus = wizard.querySelector('[data-product-gallery-status]');
            let currentStep = 0;

            const validateStep = () => {
                if (currentStep === 0 && !companyInput?.value) {
                    showToast('Choose a company before continuing.', 'error');
                    return false;
                }

                const fields = Array.from(steps[currentStep]?.querySelectorAll('input, select, textarea') ?? []);
                const invalid = fields.find((field) => !field.checkValidity());
                invalid?.reportValidity();
                return !invalid;
            };
            const render = () => {
                steps.forEach((step, index) => step.classList.toggle('active', index === currentStep));
                progressButtons.forEach((button, index) => {
                    button.classList.toggle('active', index === currentStep);
                    button.classList.toggle('done', index < currentStep);
                });
                if (back) back.style.display = currentStep > 0 ? 'inline-flex' : 'none';
                if (next) next.style.display = currentStep < steps.length - 1 ? 'inline-flex' : 'none';
                if (submit) submit.style.display = currentStep === steps.length - 1 ? 'inline-flex' : 'none';
            };
            const goTo = (target) => {
                if (target > currentStep && !validateStep()) return;
                currentStep = Math.max(0, Math.min(target, steps.length - 1));
                render();
            };

            next?.addEventListener('click', () => goTo(currentStep + 1));
            back?.addEventListener('click', () => goTo(currentStep - 1));
            progressButtons.forEach((button) => button.addEventListener('click', () => goTo(Number(button.dataset.productWizardGo))));
            imageInput?.addEventListener('change', () => {
                const file = imageInput.files?.[0];
                if (!file || !imagePreview) return;
                imagePreview.innerHTML = `<img src="${URL.createObjectURL(file)}" alt="">`;
                if (imageStatus) imageStatus.textContent = 'Selected';
                if (removeImage) removeImage.value = '0';
            });
            const updateVideoStatus = () => {
                const value = videoUrl?.value.trim() ?? '';
                const supported = /^(https?:\/\/)?(www\.|m\.)?(youtube\.com|youtu\.be|vimeo\.com|player\.vimeo\.com)\//i.test(value);
                videoStatus?.classList.toggle('valid', Boolean(value) && supported);
                videoStatus?.classList.toggle('invalid', Boolean(value) && !supported);
                const text = videoStatus?.querySelector('span');
                if (text) text.textContent = !value
                    ? 'Optional: add a YouTube or Vimeo product demo, guide, or story.'
                    : supported ? 'Supported product video link ready to publish.' : 'Use a YouTube or Vimeo video link.';
                if (reviewVideo) reviewVideo.textContent = value && supported ? 'Configured' : 'Optional';
            };
            videoUrl?.addEventListener('input', updateVideoStatus);
            const updateGalleryStatus = () => {
                const existing = galleryList?.querySelectorAll('[data-product-gallery-existing]:not(.removed)').length ?? 0;
                const selected = galleryInput?.files?.length ?? 0;
                if (galleryStatus) galleryStatus.textContent = existing + selected ? `${existing + selected} images` : 'Optional';
            };
            galleryInput?.addEventListener('change', () => {
                galleryList?.querySelectorAll('[data-product-gallery-preview]').forEach((preview) => preview.remove());
                const existing = galleryList?.querySelectorAll('[data-product-gallery-existing]:not(.removed)').length ?? 0;
                const files = Array.from(galleryInput.files ?? []);

                if (existing + files.length > 4) {
                    galleryInput.value = '';
                    showToast('A product can have up to 4 gallery images.', 'error');
                    updateGalleryStatus();
                    return;
                }

                files.forEach((file) => {
                    const preview = document.createElement('div');
                    preview.className = 'product-gallery-item';
                    preview.dataset.productGalleryPreview = '';
                    preview.innerHTML = `<img src="${URL.createObjectURL(file)}" alt="">`;
                    galleryList?.appendChild(preview);
                });
                updateGalleryStatus();
            });
            galleryList?.querySelectorAll('[data-product-gallery-remove]').forEach((button) => {
                button.addEventListener('click', () => {
                    const item = button.closest('[data-product-gallery-existing]');
                    item?.classList.add('removed');
                    if (item) item.style.display = 'none';
                    const removal = item?.querySelector('input[name="remove_gallery_paths[]"]');
                    if (removal) removal.disabled = false;
                    updateGalleryStatus();
                });
            });
            wizard.querySelector('[data-product-image-remove]')?.addEventListener('click', () => {
                if (imageInput) imageInput.value = '';
                if (removeImage) removeImage.value = '1';
                if (imagePreview) imagePreview.innerHTML = '<svg width="24" height="24"><use href="#i-image"></use></svg>';
                if (imageStatus) imageStatus.textContent = 'Removed';
            });
            form.productWizardReset = () => {
                currentStep = 0;
                updateVideoStatus();
                updateGalleryStatus();
                render();
            };
            updateVideoStatus();
            updateGalleryStatus();
            render();
        };

        document.querySelectorAll('#product-drawer form.drawer-user-form').forEach(initializeProductWizard);

        const openProductDrawer = (formKey = 'create') => {
            const targetForm = document.getElementById(`product-form-${formKey}`) ?? document.getElementById('product-form-create');

            document.querySelectorAll('#product-drawer .drawer-user-form').forEach((form) => form.classList.remove('active'));
            targetForm?.classList.add('active');

            if (productDrawerTitle && targetForm?.dataset.formTitle) {
                productDrawerTitle.textContent = targetForm.dataset.formTitle;
            }

            if (productDrawerSubtitle && targetForm?.dataset.formSubtitle) {
                productDrawerSubtitle.textContent = targetForm.dataset.formSubtitle;
            }

            productDrawer?.classList.add('active');
            productDrawer?.setAttribute('aria-hidden', 'false');
            targetForm?.productWizardReset?.();
            targetForm?.querySelector('input:not([type="hidden"]), select, textarea, button')?.focus();
        };
        const closeProductDrawer = () => {
            productDrawer?.classList.remove('active');
            productDrawer?.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-product-drawer-open]').forEach((button) => {
            button.addEventListener('click', () => openProductDrawer(button.dataset.productForm));
        });

        document.querySelectorAll('[data-product-drawer-close]').forEach((button) => {
            button.addEventListener('click', closeProductDrawer);
        });

        const productRows = Array.from(document.querySelectorAll('[data-product-row]'));
        const productSearchInput = document.querySelector('[data-product-search]');
        const productCompanyFilter = document.querySelector('[data-product-company-filter]');
        const productStatusFilter = document.querySelector('[data-product-status-filter]');
        const productBatchFilter = document.querySelector('[data-product-batch-filter]');
        const productCategoryFilter = document.querySelector('[data-product-category-filter]');
        const productNoResults = document.querySelector('[data-product-no-results]');
        const productPagination = document.querySelector('[data-product-pagination]');
        const productPageSummary = document.querySelector('[data-product-page-summary]');
        const productPageCurrent = document.querySelector('[data-product-page-current]');
        const productPagePrev = document.querySelector('[data-product-page-prev]');
        const productPageNext = document.querySelector('[data-product-page-next]');
        const productSelectAll = document.querySelector('[data-product-select-all]');
        const productSelectionBoxes = Array.from(document.querySelectorAll('[data-product-select]'));
        const productBulkForm = document.querySelector('[data-product-bulk-form]');
        const productBulkInputs = document.querySelector('[data-product-bulk-inputs]');
        const productBulkCount = document.querySelector('[data-product-bulk-count]');
        const clearProductFilters = document.querySelector('[data-clear-product-filters]');
        const productsPerPage = 8;
        let currentProductPage = 1;

        const selectedProductIds = () => productSelectionBoxes
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.value);

        const updateProductBulkInputs = () => {
            const ids = selectedProductIds();

            if (productBulkInputs) {
                productBulkInputs.innerHTML = ids.map((id) => `<input type="hidden" name="product_ids[]" value="${id}">`).join('');
            }

            productBulkForm?.classList.toggle('active', ids.length > 0);

            if (productBulkCount) {
                productBulkCount.textContent = `${ids.length} selected`;
            }
        };

        const visibleProductRows = () => productRows.filter((row) => row.style.display !== 'none');

        const syncProductSelectAll = () => {
            if (!productSelectAll) return;

            const visibleBoxes = visibleProductRows()
                .map((row) => row.querySelector('[data-product-select]'))
                .filter(Boolean);
            const checkedCount = visibleBoxes.filter((checkbox) => checkbox.checked).length;

            productSelectAll.checked = visibleBoxes.length > 0 && checkedCount === visibleBoxes.length;
            productSelectAll.indeterminate = checkedCount > 0 && checkedCount < visibleBoxes.length;
        };

        const renderProductTable = () => {
            const query = (productSearchInput?.value ?? '').trim().toLowerCase();
            const company = productCompanyFilter?.value ?? 'all';
            const status = productStatusFilter?.value ?? 'all';
            const batchState = productBatchFilter?.value ?? 'all';
            const category = productCategoryFilter?.value ?? 'all';
            const matches = productRows.filter((row) => {
                const matchesQuery = !query || row.dataset.productSearchText?.includes(query);
                const matchesCompany = company === 'all' || row.dataset.productCompany === company;
                const matchesStatus = status === 'all' || row.dataset.productStatus === status;
                const matchesBatch = batchState === 'all' || row.dataset.productBatches === batchState;
                const matchesCategory = category === 'all' || row.dataset.productCategory === category;

                return matchesQuery && matchesCompany && matchesStatus && matchesBatch && matchesCategory;
            });
            const totalPages = Math.max(1, Math.ceil(matches.length / productsPerPage));

            currentProductPage = Math.min(currentProductPage, totalPages);
            const start = (currentProductPage - 1) * productsPerPage;
            const end = start + productsPerPage;

            productRows.forEach((row) => {
                const index = matches.indexOf(row);
                row.style.display = index >= start && index < end ? '' : 'none';
            });

            productNoResults?.classList.toggle('active', productRows.length > 0 && matches.length === 0);

            if (productPagination) {
                productPagination.style.display = productRows.length > 0 ? 'flex' : 'none';
            }

            if (productPageSummary) {
                const visibleStart = matches.length === 0 ? 0 : start + 1;
                const visibleEnd = Math.min(end, matches.length);
                productPageSummary.textContent = `Showing ${visibleStart}-${visibleEnd} of ${matches.length} products`;
            }

            if (productPageCurrent) {
                productPageCurrent.textContent = `${currentProductPage} / ${totalPages}`;
            }

            if (productPagePrev) {
                productPagePrev.disabled = currentProductPage <= 1;
            }

            if (productPageNext) {
                productPageNext.disabled = currentProductPage >= totalPages;
            }

            syncProductSelectAll();
            updateProductBulkInputs();
        };

        [productSearchInput, productCompanyFilter, productStatusFilter, productBatchFilter, productCategoryFilter].forEach((control) => {
            control?.addEventListener('input', () => {
                currentProductPage = 1;
                renderProductTable();
            });
            control?.addEventListener('change', () => {
                currentProductPage = 1;
                renderProductTable();
            });
        });

        productPagePrev?.addEventListener('click', () => {
            currentProductPage = Math.max(1, currentProductPage - 1);
            renderProductTable();
        });

        productPageNext?.addEventListener('click', () => {
            currentProductPage += 1;
            renderProductTable();
        });

        productRows.forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('button, a, input, select, form')) return;
                openProductDrawer(`details-${row.dataset.productId}`);
            });
        });

        productSelectionBoxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                syncProductSelectAll();
                updateProductBulkInputs();
            });
        });

        productSelectAll?.addEventListener('change', () => {
            visibleProductRows().forEach((row) => {
                const checkbox = row.querySelector('[data-product-select]');
                if (checkbox) {
                    checkbox.checked = productSelectAll.checked;
                }
            });

            syncProductSelectAll();
            updateProductBulkInputs();
        });

        clearProductFilters?.addEventListener('click', () => {
            if (productSearchInput) productSearchInput.value = '';
            if (productCompanyFilter) productCompanyFilter.value = 'all';
            if (productStatusFilter) productStatusFilter.value = 'all';
            if (productBatchFilter) productBatchFilter.value = 'all';
            if (productCategoryFilter) productCategoryFilter.value = 'all';
            currentProductPage = 1;
            renderProductTable();
        });

        renderProductTable();

        const batchDrawer = document.getElementById('batch-drawer');
        const batchDrawerTitle = document.getElementById('batch-drawer-title');
        const batchDrawerSubtitle = document.getElementById('batch-drawer-subtitle');
        const openBatchDrawer = (formKey = 'create') => {
            const targetForm = document.getElementById(`batch-form-${formKey}`) ?? document.getElementById('batch-form-create');

            document.querySelectorAll('#batch-drawer .drawer-user-form').forEach((form) => form.classList.remove('active'));
            targetForm?.classList.add('active');

            if (batchDrawerTitle && targetForm?.dataset.formTitle) {
                batchDrawerTitle.textContent = targetForm.dataset.formTitle;
            }

            if (batchDrawerSubtitle && targetForm?.dataset.formSubtitle) {
                batchDrawerSubtitle.textContent = targetForm.dataset.formSubtitle;
            }

            batchDrawer?.classList.add('active');
            batchDrawer?.setAttribute('aria-hidden', 'false');
            targetForm?.querySelector('input:not([type="hidden"]), select, textarea, button')?.focus();
        };
        const closeBatchDrawer = () => {
            batchDrawer?.classList.remove('active');
            batchDrawer?.setAttribute('aria-hidden', 'true');
        };

        const selectBatchDrawerProduct = (productId, productLabel) => {
            const createForm = document.getElementById('batch-form-create');
            const productInput = createForm?.querySelector('[data-product-value]');
            const productSelect = createForm?.querySelector('[data-batch-product-select]');

            if (!createForm || !productInput || !productSelect) return;

            productInput.value = productId;
            productSelect.querySelectorAll('[data-smart-option]').forEach((option) => {
                option.classList.toggle('active', option.dataset.value === productId);
            });

            const label = productSelect.querySelector('[data-smart-label]');
            if (label) {
                label.textContent = productLabel || 'Search product';
            }
        };

        document.querySelectorAll('[data-batch-drawer-open]').forEach((button) => {
            button.addEventListener('click', () => openBatchDrawer(button.dataset.batchForm));
        });

        document.querySelectorAll('[data-batch-drawer-close]').forEach((button) => {
            button.addEventListener('click', closeBatchDrawer);
        });

        document.querySelectorAll('[data-create-batch-product]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                closeProductDrawer();
                openBatchDrawer('create');
                selectBatchDrawerProduct(button.dataset.createBatchProduct, button.dataset.createBatchLabel);
            });
        });

        const batchRows = Array.from(document.querySelectorAll('[data-batch-row]'));
        const batchSearchInput = document.querySelector('[data-batch-search]');
        const batchQaFilter = document.querySelector('[data-batch-qa-filter]');
        const batchStatusFilter = document.querySelector('[data-batch-status-filter]');
        const batchQrFilter = document.querySelector('[data-batch-qr-filter]');
        const batchCompanyFilter = document.querySelector('[data-batch-company-filter]');
        const batchProductFilter = document.querySelector('[data-batch-product-filter]');
        const batchNoResults = document.querySelector('[data-batch-no-results]');
        const batchPagination = document.querySelector('[data-batch-pagination]');
        const batchPageSummary = document.querySelector('[data-batch-page-summary]');
        const batchPageCurrent = document.querySelector('[data-batch-page-current]');
        const batchPagePrev = document.querySelector('[data-batch-page-prev]');
        const batchPageNext = document.querySelector('[data-batch-page-next]');
        const batchSelectAll = document.querySelector('[data-batch-select-all]');
        const batchSelectionBoxes = Array.from(document.querySelectorAll('[data-batch-select]'));
        const batchBulkForm = document.querySelector('[data-batch-bulk-form]');
        const batchBulkInputs = document.querySelector('[data-batch-bulk-inputs]');
        const batchBulkCount = document.querySelector('[data-batch-bulk-count]');
        const clearBatchFilters = document.querySelector('[data-clear-batch-filters]');
        const batchesPerPage = 8;
        let currentBatchPage = 1;

        const selectedBatchIds = () => batchSelectionBoxes
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.value);

        const updateBatchBulkInputs = () => {
            const ids = selectedBatchIds();

            if (batchBulkInputs) {
                batchBulkInputs.innerHTML = ids.map((id) => `<input type="hidden" name="batch_ids[]" value="${id}">`).join('');
            }

            batchBulkForm?.classList.toggle('active', ids.length > 0);

            if (batchBulkCount) {
                batchBulkCount.textContent = `${ids.length} selected`;
            }
        };

        const visibleBatchRows = () => batchRows.filter((row) => row.style.display !== 'none');

        const syncBatchSelectAll = () => {
            if (!batchSelectAll) return;

            const visibleBoxes = visibleBatchRows()
                .map((row) => row.querySelector('[data-batch-select]'))
                .filter(Boolean);
            const checkedCount = visibleBoxes.filter((checkbox) => checkbox.checked).length;

            batchSelectAll.checked = visibleBoxes.length > 0 && checkedCount === visibleBoxes.length;
            batchSelectAll.indeterminate = checkedCount > 0 && checkedCount < visibleBoxes.length;
        };

        const renderBatchTable = () => {
            const query = (batchSearchInput?.value ?? '').trim().toLowerCase();
            const qa = batchQaFilter?.value ?? 'all';
            const status = batchStatusFilter?.value ?? 'all';
            const qr = batchQrFilter?.value ?? 'all';
            const company = batchCompanyFilter?.value ?? 'all';
            const product = batchProductFilter?.value ?? 'all';
            const matches = batchRows.filter((row) => {
                const matchesQuery = !query || row.dataset.batchSearchText?.includes(query);
                const matchesQa = qa === 'all' || row.dataset.batchQa === qa;
                const matchesStatus = status === 'all' || row.dataset.batchStatus === status;
                const matchesQr = qr === 'all' || row.dataset.batchQr === qr;
                const matchesCompany = company === 'all' || row.dataset.batchCompany === company;
                const matchesProduct = product === 'all' || row.dataset.batchProduct === product;

                return matchesQuery && matchesQa && matchesStatus && matchesQr && matchesCompany && matchesProduct;
            });
            const totalPages = Math.max(1, Math.ceil(matches.length / batchesPerPage));

            currentBatchPage = Math.min(currentBatchPage, totalPages);
            const start = (currentBatchPage - 1) * batchesPerPage;
            const end = start + batchesPerPage;

            batchRows.forEach((row) => {
                const index = matches.indexOf(row);
                row.style.display = index >= start && index < end ? '' : 'none';
            });

            batchNoResults?.classList.toggle('active', batchRows.length > 0 && matches.length === 0);

            if (batchPagination) {
                batchPagination.style.display = batchRows.length > 0 ? 'flex' : 'none';
            }

            if (batchPageSummary) {
                const visibleStart = matches.length === 0 ? 0 : start + 1;
                const visibleEnd = Math.min(end, matches.length);
                batchPageSummary.textContent = `Showing ${visibleStart}-${visibleEnd} of ${matches.length} batches`;
            }

            if (batchPageCurrent) {
                batchPageCurrent.textContent = `${currentBatchPage} / ${totalPages}`;
            }

            if (batchPagePrev) {
                batchPagePrev.disabled = currentBatchPage <= 1;
            }

            if (batchPageNext) {
                batchPageNext.disabled = currentBatchPage >= totalPages;
            }

            syncBatchSelectAll();
            updateBatchBulkInputs();
        };

        [batchSearchInput, batchQaFilter, batchStatusFilter, batchQrFilter, batchCompanyFilter, batchProductFilter].forEach((control) => {
            control?.addEventListener('input', () => {
                currentBatchPage = 1;
                renderBatchTable();
            });
            control?.addEventListener('change', () => {
                currentBatchPage = 1;
                renderBatchTable();
            });
        });

        batchPagePrev?.addEventListener('click', () => {
            currentBatchPage = Math.max(1, currentBatchPage - 1);
            renderBatchTable();
        });

        batchPageNext?.addEventListener('click', () => {
            currentBatchPage += 1;
            renderBatchTable();
        });

        batchRows.forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('button, a, input, select, form')) return;
                openBatchDrawer(`details-${row.dataset.batchId}`);
            });
        });

        batchSelectionBoxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                syncBatchSelectAll();
                updateBatchBulkInputs();
            });
        });

        batchSelectAll?.addEventListener('change', () => {
            visibleBatchRows().forEach((row) => {
                const checkbox = row.querySelector('[data-batch-select]');
                if (checkbox) {
                    checkbox.checked = batchSelectAll.checked;
                }
            });

            syncBatchSelectAll();
            updateBatchBulkInputs();
        });

        clearBatchFilters?.addEventListener('click', () => {
            if (batchSearchInput) batchSearchInput.value = '';
            if (batchQaFilter) batchQaFilter.value = 'all';
            if (batchStatusFilter) batchStatusFilter.value = 'all';
            if (batchQrFilter) batchQrFilter.value = 'all';
            if (batchCompanyFilter) batchCompanyFilter.value = 'all';
            if (batchProductFilter) batchProductFilter.value = 'all';
            currentBatchPage = 1;
            renderBatchTable();
        });

        renderBatchTable();

        const orderDrawer = document.getElementById('order-drawer');
        const orderDrawerTitle = document.getElementById('order-drawer-title');
        const orderDrawerSubtitle = document.getElementById('order-drawer-subtitle');
        const labelPreviewContent = {
            hologram: ['Premium visual authentication layer', 'Best for retail products and customer trust', 'Works well with batch QR labels'],
            'tamper-proof': ['Shows visible evidence when removed', 'Best for seals, lids, and cartons', 'Useful for distribution-chain control'],
            'security seal': ['Strong closure protection for packages', 'Best for cartons and high-risk stock', 'Pairs well with delivery inspection'],
            combo: ['Combines hologram and tamper evidence', 'Best for high-value or sensitive products', 'Maximum scan-and-physical trust signal'],
        };
        const renderLabelPreview = (preview, type) => {
            if (!preview) return;

            const points = labelPreviewContent[type] ?? labelPreviewContent.hologram;
            preview.innerHTML = `
                <div class="role-preview-title">${type.charAt(0).toUpperCase() + type.slice(1)} label fit</div>
                <div class="role-preview-list">
                    ${points.map((point) => `<span>${point}</span>`).join('')}
                </div>
            `;
        };
        const openOrderDrawer = (formKey = 'create') => {
            const targetForm = document.getElementById(`order-form-${formKey}`) ?? document.getElementById('order-form-create');

            document.querySelectorAll('#order-drawer .drawer-user-form').forEach((form) => form.classList.remove('active'));
            targetForm?.classList.add('active');

            if (orderDrawerTitle && targetForm?.dataset.formTitle) {
                orderDrawerTitle.textContent = targetForm.dataset.formTitle;
            }

            if (orderDrawerSubtitle && targetForm?.dataset.formSubtitle) {
                orderDrawerSubtitle.textContent = targetForm.dataset.formSubtitle;
            }

            orderDrawer?.classList.add('active');
            orderDrawer?.setAttribute('aria-hidden', 'false');
            targetForm?.querySelector('input, select, textarea, button')?.focus();
        };
        const closeOrderDrawer = () => {
            orderDrawer?.classList.remove('active');
            orderDrawer?.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-order-drawer-open]').forEach((button) => {
            button.addEventListener('click', () => openOrderDrawer(button.dataset.orderForm));
        });

        document.querySelectorAll('[data-order-drawer-close]').forEach((button) => {
            button.addEventListener('click', closeOrderDrawer);
        });

        document.querySelectorAll('[data-label-type-select]').forEach((select) => {
            const preview = select.closest('.drawer-user-form')?.querySelector('[data-label-preview]');
            renderLabelPreview(preview, select.value);
            select.addEventListener('change', () => renderLabelPreview(preview, select.value));
        });

        document.querySelectorAll('[data-static-label]').forEach((preview) => {
            renderLabelPreview(preview, preview.dataset.staticLabel);
        });

        const closeSmartSelects = (except = null) => {
            document.querySelectorAll('.smart-select.open').forEach((select) => {
                if (select !== except) {
                    select.classList.remove('open');
                }
            });
        };

        const initializeSuitabilityEditor = (editor) => {
            const list = editor.querySelector('[data-suitability-list]');
            const template = editor.querySelector('[data-suitability-template]');
            const empty = editor.querySelector('[data-suitability-empty]');

            if (!list || !template) return;

            const syncRows = () => {
                const rows = Array.from(list.querySelectorAll('[data-suitability-item]'));
                rows.forEach((row, index) => {
                    row.querySelector('[data-suitability-number]').textContent = index + 1;
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/suitability_items\[[^\]]+\]/, `suitability_items[${index}]`);
                    });
                    row.querySelector('[data-suitability-up]').disabled = index === 0;
                    row.querySelector('[data-suitability-down]').disabled = index === rows.length - 1;
                });
                empty?.classList.toggle('hidden', rows.length > 0);
            };

            const bindRow = (row) => {
                const labelInput = row.querySelector('[data-suitability-label]');
                const summary = row.querySelector('[data-suitability-summary]');
                const syncSummary = () => summary.textContent = labelInput.value.trim() || 'New guidance';

                labelInput?.addEventListener('input', syncSummary);
                row.querySelector('[data-suitability-remove]')?.addEventListener('click', () => {
                    row.remove();
                    syncRows();
                });
                row.querySelector('[data-suitability-up]')?.addEventListener('click', () => {
                    if (row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
                    syncRows();
                });
                row.querySelector('[data-suitability-down]')?.addEventListener('click', () => {
                    if (row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
                    syncRows();
                });
                syncSummary();
            };

            list.querySelectorAll('[data-suitability-item]').forEach(bindRow);
            editor.querySelector('[data-suitability-add]')?.addEventListener('click', () => {
                const index = list.querySelectorAll('[data-suitability-item]').length;
                if (index >= 20) {
                    showToast('A product can have up to 20 suitability entries.', 'error');
                    return;
                }

                const wrap = document.createElement('div');
                wrap.innerHTML = template.innerHTML.replaceAll('__INDEX__', index).trim();
                const row = wrap.firstElementChild;
                list.appendChild(row);
                bindRow(row);
                syncRows();
                row.querySelector('[data-suitability-label]')?.focus();
            });
            syncRows();
        };

        document.querySelectorAll('[data-suitability-editor]').forEach(initializeSuitabilityEditor);

        const initializeProductFaqEditor = (editor) => {
            const list = editor.querySelector('[data-product-faq-list]');
            const template = editor.querySelector('[data-product-faq-template]');
            const empty = editor.querySelector('[data-product-faq-empty]');

            if (!list || !template) return;

            const syncRows = () => {
                const rows = Array.from(list.querySelectorAll('[data-product-faq-item]'));
                rows.forEach((row, index) => {
                    row.querySelector('[data-product-faq-number]').textContent = index + 1;
                    row.querySelectorAll('[name]').forEach((field) => {
                        field.name = field.name.replace(/faq_items\[[^\]]+\]/, `faq_items[${index}]`);
                    });
                    row.querySelector('[data-product-faq-up]').disabled = index === 0;
                    row.querySelector('[data-product-faq-down]').disabled = index === rows.length - 1;
                });
                empty?.classList.toggle('hidden', rows.length > 0);
            };

            const bindRow = (row) => {
                const question = row.querySelector('[data-product-faq-question]');
                const summary = row.querySelector('[data-product-faq-summary]');
                const syncSummary = () => summary.textContent = question.value.trim() || 'New question';

                question?.addEventListener('input', syncSummary);
                row.querySelector('[data-product-faq-remove]')?.addEventListener('click', () => {
                    row.remove();
                    syncRows();
                });
                row.querySelector('[data-product-faq-up]')?.addEventListener('click', () => {
                    if (row.previousElementSibling) list.insertBefore(row, row.previousElementSibling);
                    syncRows();
                });
                row.querySelector('[data-product-faq-down]')?.addEventListener('click', () => {
                    if (row.nextElementSibling) list.insertBefore(row.nextElementSibling, row);
                    syncRows();
                });
                syncSummary();
            };

            list.querySelectorAll('[data-product-faq-item]').forEach(bindRow);
            editor.querySelector('[data-product-faq-add]')?.addEventListener('click', () => {
                const index = list.querySelectorAll('[data-product-faq-item]').length;
                if (index >= 12) {
                    showToast('A product can have up to 12 FAQs.', 'error');
                    return;
                }

                const wrap = document.createElement('div');
                wrap.innerHTML = template.innerHTML.replaceAll('__INDEX__', index).trim();
                const row = wrap.firstElementChild;
                list.appendChild(row);
                bindRow(row);
                syncRows();
                row.querySelector('[data-product-faq-question]')?.focus();
            });
            syncRows();
        };

        document.querySelectorAll('[data-product-faq-editor]').forEach(initializeProductFaqEditor);

        const initializeProductCompanySelects = (form) => {
            const companyInput = form.querySelector('[data-company-value]');
            const companySelect = form.querySelector('[data-product-company-select]');
            const categorySelect = form.querySelector('[data-product-category-select]');

            if (!companyInput || !companySelect) return;

            const toggle = companySelect.querySelector('[data-smart-toggle]');
            const search = companySelect.querySelector('[data-smart-search]');
            const label = companySelect.querySelector('[data-smart-label]');
            const options = Array.from(companySelect.querySelectorAll('[data-smart-option]'));
            const empty = companySelect.querySelector('[data-smart-empty]');
            const renderCategories = () => {
                if (!categorySelect) return;

                Array.from(categorySelect.options).forEach((option) => {
                    option.hidden = Boolean(option.value) && option.dataset.companyId !== companyInput.value;
                });

                if (categorySelect.selectedOptions[0]?.hidden) {
                    categorySelect.value = '';
                }
            };

            const renderOptions = () => {
                const query = (search?.value ?? '').trim().toLowerCase();
                let visibleCount = 0;

                options.forEach((option) => {
                    const matches = !query || option.dataset.search?.includes(query) || option.textContent.toLowerCase().includes(query);
                    option.hidden = !matches;
                    if (matches) visibleCount += 1;
                });

                empty?.classList.toggle('active', visibleCount === 0);
            };

            toggle?.addEventListener('click', (event) => {
                event.stopPropagation();
                const willOpen = !companySelect.classList.contains('open');
                closeSmartSelects(companySelect);
                companySelect.classList.toggle('open', willOpen);
                if (willOpen) {
                    search?.focus();
                    renderOptions();
                }
            });

            search?.addEventListener('click', (event) => event.stopPropagation());
            search?.addEventListener('input', renderOptions);

            options.forEach((option) => {
                option.addEventListener('click', (event) => {
                    event.stopPropagation();
                    if (option.hidden) return;

                    companyInput.value = option.dataset.value ?? '';
                    options.forEach((item) => item.classList.remove('active'));
                    option.classList.add('active');
                    if (label) label.textContent = option.textContent.trim();
                    companySelect.classList.remove('open');
                    renderCategories();
                });
            });

            form.addEventListener('submit', (event) => {
                if (companyInput.value) return;

                event.preventDefault();
                companySelect.classList.add('open');
                search?.focus();
                renderOptions();
            });

            renderOptions();
            renderCategories();
        };

        document.querySelectorAll('#product-drawer .drawer-user-form').forEach(initializeProductCompanySelects);

        const initializeBatchProductSelects = (form) => {
            const productInput = form.querySelector('[data-product-value]');
            const productSelect = form.querySelector('[data-batch-product-select]');

            if (!productInput || !productSelect) return;

            const toggle = productSelect.querySelector('[data-smart-toggle]');
            const search = productSelect.querySelector('[data-smart-search]');
            const label = productSelect.querySelector('[data-smart-label]');
            const options = Array.from(productSelect.querySelectorAll('[data-smart-option]'));
            const empty = productSelect.querySelector('[data-smart-empty]');

            const renderOptions = () => {
                const query = (search?.value ?? '').trim().toLowerCase();
                let visibleCount = 0;

                options.forEach((option) => {
                    const matches = !query || option.dataset.search?.includes(query) || option.textContent.toLowerCase().includes(query);
                    option.hidden = !matches;
                    if (matches) visibleCount += 1;
                });

                empty?.classList.toggle('active', visibleCount === 0);
            };

            toggle?.addEventListener('click', (event) => {
                event.stopPropagation();
                const willOpen = !productSelect.classList.contains('open');
                closeSmartSelects(productSelect);
                productSelect.classList.toggle('open', willOpen);
                if (willOpen) {
                    search?.focus();
                    renderOptions();
                }
            });

            search?.addEventListener('click', (event) => event.stopPropagation());
            search?.addEventListener('input', renderOptions);

            options.forEach((option) => {
                option.addEventListener('click', (event) => {
                    event.stopPropagation();
                    if (option.hidden) return;

                    productInput.value = option.dataset.value ?? '';
                    options.forEach((item) => item.classList.remove('active'));
                    option.classList.add('active');
                    if (label) label.textContent = option.textContent.trim();
                    productSelect.classList.remove('open');
                });
            });

            form.addEventListener('submit', (event) => {
                if (productInput.value) return;

                event.preventDefault();
                productSelect.classList.add('open');
                search?.focus();
                renderOptions();
            });

            renderOptions();
        };

        document.querySelectorAll('#batch-drawer .drawer-user-form').forEach(initializeBatchProductSelects);

        const initializeOrderSmartSelects = (form) => {
            const companyInput = form.querySelector('[data-company-value]');
            const batchInput = form.querySelector('[data-batch-value]');
            const companySelect = form.querySelector('[data-smart-company]');
            const batchSelect = form.querySelector('[data-smart-batch]');

            if (!companyInput || !batchInput || !companySelect || !batchSelect) return;

            const setupSmartSelect = (select, onChoose, filterOption) => {
                const toggle = select.querySelector('[data-smart-toggle]');
                const search = select.querySelector('[data-smart-search]');
                const options = Array.from(select.querySelectorAll('[data-smart-option]'));
                const empty = select.querySelector('[data-smart-empty]');

                const renderOptions = () => {
                    const query = (search?.value ?? '').trim().toLowerCase();
                    let visibleCount = 0;

                    options.forEach((option) => {
                        const matchesQuery = !query || option.dataset.search?.includes(query) || option.textContent.toLowerCase().includes(query);
                        const visible = matchesQuery && (!filterOption || filterOption(option));
                        option.hidden = !visible;
                        if (visible) visibleCount += 1;
                    });

                    empty?.classList.toggle('active', visibleCount === 0);
                };

                toggle?.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const willOpen = !select.classList.contains('open');
                    closeSmartSelects(select);
                    select.classList.toggle('open', willOpen);
                    if (willOpen) {
                        search?.focus();
                        renderOptions();
                    }
                });

                search?.addEventListener('click', (event) => event.stopPropagation());
                search?.addEventListener('input', renderOptions);

                options.forEach((option) => {
                    option.addEventListener('click', (event) => {
                        event.stopPropagation();
                        if (option.hidden) return;

                        options.forEach((item) => item.classList.remove('active'));
                        option.classList.add('active');
                        select.querySelector('[data-smart-label]').textContent = option.textContent.trim();
                        onChoose(option);
                        select.classList.remove('open');
                    });
                });

                renderOptions();

                return renderOptions;
            };

            let renderBatchOptions = null;

            const chooseBatch = (option) => {
                batchInput.value = option.dataset.value ?? '';
            };

            const chooseCompany = (option) => {
                companyInput.value = option.dataset.value ?? '';
                const generalBatch = batchSelect.querySelector('[data-smart-option][data-value=""]');

                if (generalBatch) {
                    batchSelect.querySelectorAll('[data-smart-option]').forEach((item) => item.classList.remove('active'));
                    generalBatch.classList.add('active');
                    batchSelect.querySelector('[data-smart-label]').textContent = generalBatch.textContent.trim();
                    chooseBatch(generalBatch);
                }

                batchSelect.querySelector('[data-smart-search]').value = '';
                renderBatchOptions?.();
            };

            setupSmartSelect(companySelect, chooseCompany);
            renderBatchOptions = setupSmartSelect(
                batchSelect,
                chooseBatch,
                (option) => option.dataset.companyId === 'all' || option.dataset.companyId === companyInput.value,
            );
            renderBatchOptions?.();
        };

        document.querySelectorAll('#order-drawer .drawer-user-form').forEach(initializeOrderSmartSelects);

        document.addEventListener('click', () => closeSmartSelects());

        const orderRows = Array.from(document.querySelectorAll('[data-order-row]'));
        const orderSearchInput = document.querySelector('[data-order-search]');
        const orderTypeFilter = document.querySelector('[data-order-type-filter]');
        const orderStatusFilter = document.querySelector('[data-order-status-filter]');
        const orderNoResults = document.querySelector('[data-order-no-results]');
        const orderPagination = document.querySelector('[data-order-pagination]');
        const orderPageSummary = document.querySelector('[data-order-page-summary]');
        const orderPageCurrent = document.querySelector('[data-order-page-current]');
        const orderPagePrev = document.querySelector('[data-order-page-prev]');
        const orderPageNext = document.querySelector('[data-order-page-next]');
        const orderSelectAll = document.querySelector('[data-order-select-all]');
        const orderSelectionBoxes = Array.from(document.querySelectorAll('[data-order-select]'));
        const orderBulkForm = document.querySelector('[data-order-bulk-form]');
        const orderBulkInputs = document.querySelector('[data-order-bulk-inputs]');
        const orderBulkCount = document.querySelector('[data-order-bulk-count]');
        const clearOrderFilters = document.querySelector('[data-clear-order-filters]');
        const ordersPerPage = 8;
        let currentOrderPage = 1;

        const selectedOrderIds = () => orderSelectionBoxes
            .filter((checkbox) => checkbox.checked)
            .map((checkbox) => checkbox.value);

        const updateOrderBulkInputs = () => {
            const ids = selectedOrderIds();

            if (orderBulkInputs) {
                orderBulkInputs.innerHTML = ids.map((id) => `<input type="hidden" name="order_ids[]" value="${id}">`).join('');
            }

            orderBulkForm?.classList.toggle('active', ids.length > 0);

            if (orderBulkCount) {
                orderBulkCount.textContent = `${ids.length} selected`;
            }
        };

        const visibleOrderRows = () => orderRows.filter((row) => row.style.display !== 'none');

        const syncOrderSelectAll = () => {
            if (!orderSelectAll) return;

            const visibleBoxes = visibleOrderRows()
                .map((row) => row.querySelector('[data-order-select]'))
                .filter(Boolean);
            const checkedCount = visibleBoxes.filter((checkbox) => checkbox.checked).length;

            orderSelectAll.checked = visibleBoxes.length > 0 && checkedCount === visibleBoxes.length;
            orderSelectAll.indeterminate = checkedCount > 0 && checkedCount < visibleBoxes.length;
        };

        const renderOrderTable = () => {
            const query = (orderSearchInput?.value ?? '').trim().toLowerCase();
            const type = orderTypeFilter?.value ?? 'all';
            const status = orderStatusFilter?.value ?? 'all';
            const matches = orderRows.filter((row) => {
                const matchesQuery = !query || row.dataset.orderSearchText?.includes(query);
                const matchesType = type === 'all' || row.dataset.orderType === type;
                const matchesStatus = status === 'all' || row.dataset.orderStatus === status;

                return matchesQuery && matchesType && matchesStatus;
            });
            const totalPages = Math.max(1, Math.ceil(matches.length / ordersPerPage));

            currentOrderPage = Math.min(currentOrderPage, totalPages);
            const start = (currentOrderPage - 1) * ordersPerPage;
            const end = start + ordersPerPage;

            orderRows.forEach((row) => {
                const index = matches.indexOf(row);
                row.style.display = index >= start && index < end ? '' : 'none';
            });

            orderNoResults?.classList.toggle('active', orderRows.length > 0 && matches.length === 0);

            if (orderPagination) {
                orderPagination.style.display = orderRows.length > 0 ? 'flex' : 'none';
            }

            if (orderPageSummary) {
                const visibleStart = matches.length === 0 ? 0 : start + 1;
                const visibleEnd = Math.min(end, matches.length);
                orderPageSummary.textContent = `Showing ${visibleStart}-${visibleEnd} of ${matches.length} orders`;
            }

            if (orderPageCurrent) {
                orderPageCurrent.textContent = `${currentOrderPage} / ${totalPages}`;
            }

            if (orderPagePrev) {
                orderPagePrev.disabled = currentOrderPage <= 1;
            }

            if (orderPageNext) {
                orderPageNext.disabled = currentOrderPage >= totalPages;
            }

            syncOrderSelectAll();
            updateOrderBulkInputs();
        };

        [orderSearchInput, orderTypeFilter, orderStatusFilter].forEach((control) => {
            control?.addEventListener('input', () => {
                currentOrderPage = 1;
                renderOrderTable();
            });
            control?.addEventListener('change', () => {
                currentOrderPage = 1;
                renderOrderTable();
            });
        });

        orderPagePrev?.addEventListener('click', () => {
            currentOrderPage = Math.max(1, currentOrderPage - 1);
            renderOrderTable();
        });

        orderPageNext?.addEventListener('click', () => {
            currentOrderPage += 1;
            renderOrderTable();
        });

        orderRows.forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('button, a, input, select, form')) return;
                openOrderDrawer(`details-${row.dataset.orderId}`);
            });
        });

        orderSelectionBoxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                syncOrderSelectAll();
                updateOrderBulkInputs();
            });
        });

        orderSelectAll?.addEventListener('change', () => {
            visibleOrderRows().forEach((row) => {
                const checkbox = row.querySelector('[data-order-select]');
                if (checkbox) {
                    checkbox.checked = orderSelectAll.checked;
                }
            });

            syncOrderSelectAll();
            updateOrderBulkInputs();
        });

        clearOrderFilters?.addEventListener('click', () => {
            if (orderSearchInput) orderSearchInput.value = '';
            if (orderTypeFilter) orderTypeFilter.value = 'all';
            if (orderStatusFilter) orderStatusFilter.value = 'all';
            currentOrderPage = 1;
            renderOrderTable();
        });

        renderOrderTable();

        const scanDrawer = document.getElementById('scan-drawer');
        const scanDrawerTitle = document.getElementById('scan-drawer-title');
        const scanDrawerSubtitle = document.getElementById('scan-drawer-subtitle');
        const openScanDrawer = (formKey) => {
            const targetPanel = document.getElementById(`scan-form-${formKey}`);

            document.querySelectorAll('#scan-drawer .drawer-user-form').forEach((panel) => panel.classList.remove('active'));
            targetPanel?.classList.add('active');

            if (scanDrawerTitle && targetPanel?.dataset.formTitle) {
                scanDrawerTitle.textContent = targetPanel.dataset.formTitle;
            }

            if (scanDrawerSubtitle && targetPanel?.dataset.formSubtitle) {
                scanDrawerSubtitle.textContent = targetPanel.dataset.formSubtitle;
            }

            scanDrawer?.classList.add('active');
            scanDrawer?.setAttribute('aria-hidden', 'false');
        };
        const closeScanDrawer = () => {
            scanDrawer?.classList.remove('active');
            scanDrawer?.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-scan-drawer-open]').forEach((button) => {
            button.addEventListener('click', () => openScanDrawer(button.dataset.scanForm));
        });

        document.querySelectorAll('[data-scan-drawer-close]').forEach((button) => {
            button.addEventListener('click', closeScanDrawer);
        });

        const scanRows = Array.from(document.querySelectorAll('[data-scan-row]'));
        const scanSearchInput = document.querySelector('[data-scan-search]');
        const scanStatusFilter = document.querySelector('[data-scan-status-filter]');
        const scanDeviceFilter = document.querySelector('[data-scan-device-filter]');
        const scanNoResults = document.querySelector('[data-scan-no-results]');
        const scanPagination = document.querySelector('[data-scan-pagination]');
        const scanPageSummary = document.querySelector('[data-scan-page-summary]');
        const scanPageCurrent = document.querySelector('[data-scan-page-current]');
        const scanPagePrev = document.querySelector('[data-scan-page-prev]');
        const scanPageNext = document.querySelector('[data-scan-page-next]');
        const clearScanFilters = document.querySelector('[data-clear-scan-filters]');
        const scansPerPage = 10;
        let currentScanPage = 1;

        const renderScanTable = () => {
            const query = (scanSearchInput?.value ?? '').trim().toLowerCase();
            const status = scanStatusFilter?.value ?? 'all';
            const device = scanDeviceFilter?.value ?? 'all';
            const matches = scanRows.filter((row) => {
                const matchesQuery = !query || row.dataset.scanSearchText?.includes(query);
                const matchesStatus = status === 'all' || row.dataset.scanStatus === status;
                const matchesDevice = device === 'all' || row.dataset.scanDevice === device;

                return matchesQuery && matchesStatus && matchesDevice;
            });
            const totalPages = Math.max(1, Math.ceil(matches.length / scansPerPage));

            currentScanPage = Math.min(currentScanPage, totalPages);
            const start = (currentScanPage - 1) * scansPerPage;
            const end = start + scansPerPage;

            scanRows.forEach((row) => {
                const index = matches.indexOf(row);
                row.style.display = index >= start && index < end ? '' : 'none';
            });

            scanNoResults?.classList.toggle('active', scanRows.length > 0 && matches.length === 0);

            if (scanPagination) {
                scanPagination.style.display = scanRows.length > 0 ? 'flex' : 'none';
            }

            if (scanPageSummary) {
                const visibleStart = matches.length === 0 ? 0 : start + 1;
                const visibleEnd = Math.min(end, matches.length);
                scanPageSummary.textContent = `Showing ${visibleStart}-${visibleEnd} of ${matches.length} scans`;
            }

            if (scanPageCurrent) {
                scanPageCurrent.textContent = `${currentScanPage} / ${totalPages}`;
            }

            if (scanPagePrev) {
                scanPagePrev.disabled = currentScanPage <= 1;
            }

            if (scanPageNext) {
                scanPageNext.disabled = currentScanPage >= totalPages;
            }
        };

        [scanSearchInput, scanStatusFilter, scanDeviceFilter].forEach((control) => {
            control?.addEventListener('input', () => {
                currentScanPage = 1;
                renderScanTable();
            });
            control?.addEventListener('change', () => {
                currentScanPage = 1;
                renderScanTable();
            });
        });

        scanPagePrev?.addEventListener('click', () => {
            currentScanPage = Math.max(1, currentScanPage - 1);
            renderScanTable();
        });

        scanPageNext?.addEventListener('click', () => {
            currentScanPage += 1;
            renderScanTable();
        });

        scanRows.forEach((row) => {
            row.addEventListener('click', (event) => {
                if (event.target.closest('button, a, input, select, form')) return;
                openScanDrawer(`details-${row.dataset.scanId}`);
            });
        });

        clearScanFilters?.addEventListener('click', () => {
            if (scanSearchInput) scanSearchInput.value = '';
            if (scanStatusFilter) scanStatusFilter.value = 'all';
            if (scanDeviceFilter) scanDeviceFilter.value = 'all';
            currentScanPage = 1;
            renderScanTable();
        });

        renderScanTable();

        document.querySelectorAll('[data-date-dropdown]').forEach((wrap) => {
            const toggle = wrap.querySelector('[data-date-toggle]');
            let pendingStartDate = null;

            toggle?.addEventListener('click', (event) => {
                event.stopPropagation();
                const isOpen = wrap.classList.toggle('open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            wrap.querySelector('.date-dropdown')?.addEventListener('click', (event) => {
                event.stopPropagation();
            });

            wrap.querySelectorAll('[data-calendar-day]').forEach((day) => {
                day.addEventListener('click', () => {
                    const selectedDate = day.dataset.calendarDay;

                    if (!pendingStartDate) {
                        pendingStartDate = selectedDate;
                        wrap.querySelectorAll('[data-calendar-day]').forEach((item) => item.classList.remove('range-start', 'range-end', 'in-range'));
                        day.classList.add('range-start');
                        return;
                    }

                    const dates = [pendingStartDate, selectedDate].sort();
                    const url = new URL(window.location.href);
                    url.searchParams.set('range', 'custom');
                    url.searchParams.set('start_date', dates[0]);
                    url.searchParams.set('end_date', dates[1]);
                    window.location.href = url.toString();
                });
            });
        });

        document.addEventListener('click', () => {
            document.querySelectorAll('[data-date-dropdown].open').forEach((wrap) => {
                wrap.classList.remove('open');
                wrap.querySelector('[data-date-toggle]')?.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;

            closeUserDrawer();
            closeCompanyDrawer();
            closeCategoryDrawer();
            closeProductDrawer();
            closeBatchDrawer();
            closeOrderDrawer();
            closeScanDrawer();
            closeDeleteAlert();

            document.querySelectorAll('[data-date-dropdown].open').forEach((wrap) => {
                wrap.classList.remove('open');
                wrap.querySelector('[data-date-toggle]')?.setAttribute('aria-expanded', 'false');
            });
        });

        document.querySelectorAll('[data-copy]').forEach((button) => {
            button.addEventListener('click', async () => {
                await navigator.clipboard?.writeText(button.dataset.copy);
                const text = button.textContent;
                button.textContent = 'Copied';
                setTimeout(() => button.textContent = text, 1200);
            });
        });

        const xLabelsEl = document.getElementById('a-x-labels');
        if (xLabelsEl && xLabelsEl.children.length === 0) {
            const start = new Date(2026, 3, 28);
            for (let i = 0; i < 30; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                const span = document.createElement('span');
                span.textContent = `${d.toLocaleString('default', { month: 'short' })} ${d.getDate()}`;
                span.style.cssText = 'flex:1;font-size:10px;color:#9ca3af;transform:rotate(-45deg);transform-origin:top left;white-space:nowrap;display:block;';
                xLabelsEl.appendChild(span);
            }
        }
    </script>
