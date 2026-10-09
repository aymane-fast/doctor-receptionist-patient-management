                    <div class="rounded-2xl border border-teal-100 bg-teal-50/50 p-5">
                        <div class="flex items-start gap-3">
                            <div class="icon-tile flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"><i class="fas fa-stethoscope"></i></div>
                            <div class="flex-1">
                                <label for="clinic_specialty" class="block text-lg font-semibold text-slate-900">Practice specialty</label>
                                <p class="mt-1 text-sm text-slate-600">One shared clinic workflow. Pediatrics adds child identity and parent/guardian contacts. General practice, dentistry and cardiology use the standard workflow.</p>
                                <select name="clinic_specialty" id="clinic_specialty" class="form-control mt-4 w-full" required>
                                    @foreach($specialties as $key => $specialty)
                                        <option value="{{ $key }}"
                                            data-duration="{{ $specialty['defaults']['appointment_duration'] }}"
                                            data-mode="{{ $specialty['defaults']['appointment_mode'] }}"
                                            data-description="{{ $specialty['description'] }}"
                                            @selected(old('clinic_specialty', 'general_practice') === $key)>
                                            {{ $specialty['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <p id="specialty-description" class="mt-3 text-sm text-teal-900"></p>
                                <p class="mt-1 text-xs text-slate-500">Changing it suggests a duration and workflow; both remain editable in the next steps and later in Settings.</p>
                            </div>
                        </div>
                    </div>
