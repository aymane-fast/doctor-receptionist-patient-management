@extends('layouts.app')

@section('title', 'Export des Données')

@section('content')
<div class="space-y-6">
    <!-- Modern Header -->
    <div class="glass-effect rounded-2xl p-6 modern-shadow">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
                    <i class="fas fa-download text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        <span class="text-gradient">Export des Données</span>
                    </h1>
                    <p class="text-gray-600 mt-1">Exportez les données de la clinique dans des formats organisés et professionnels</p>
                </div>
            </div>
            <button onclick="history.back()" class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 py-2 rounded-xl font-medium transition-all duration-200 flex items-center space-x-2">
                <i class="fas fa-arrow-left text-sm"></i>
                <span>Retour</span>
            </button>
        </div>
    </div>

    <!-- Export Form -->
    <div class="glass-effect rounded-2xl modern-shadow overflow-hidden">
        <form action="{{ route('exports.export') }}" method="POST" id="exportForm" class="space-y-6">
            @csrf
            
            <!-- Data Selection -->
            <div class="p-6 pb-0">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-database text-white text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Sélectionner les Données à Exporter</h3>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Patients -->
                    <div class="relative">
                        <input type="checkbox" id="patients" name="data_types[]" value="patients" 
                               class="sr-only peer">
                        <label for="patients" class="flex items-center p-4 bg-white border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-users text-purple-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">Patients</div>
                                    <div class="text-sm text-gray-600">Dossiers patients et démographie</div>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-blue-500 peer-checked:border-blue-500 flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs opacity-0 peer-checked:opacity-100"></i>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Appointments -->
                    <div class="relative">
                        <input type="checkbox" id="appointments" name="data_types[]" value="appointments" 
                               class="sr-only peer">
                        <label for="appointments" class="flex items-center p-4 bg-white border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-blue-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">Rendez-vous</div>
                                    <div class="text-sm text-gray-600">Planification et historique des visites</div>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-blue-500 peer-checked:border-blue-500 flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs opacity-0 peer-checked:opacity-100"></i>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Medical Records -->
                    <div class="relative">
                        <input type="checkbox" id="medical_records" name="data_types[]" value="medical_records" 
                               class="sr-only peer">
                        <label for="medical_records" class="flex items-center p-4 bg-white border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-medical text-emerald-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">Dossiers Médicaux</div>
                                    <div class="text-sm text-gray-600">Diagnostics et notes cliniques</div>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-blue-500 peer-checked:border-blue-500 flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs opacity-0 peer-checked:opacity-100"></i>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Prescriptions -->
                    <div class="relative">
                        <input type="checkbox" id="prescriptions" name="data_types[]" value="prescriptions" 
                               class="sr-only peer">
                        <label for="prescriptions" class="flex items-center p-4 bg-white border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-pink-100 to-pink-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-pills text-pink-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">Ordonnances</div>
                                    <div class="text-sm text-gray-600">Médicaments et posologies</div>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-blue-500 peer-checked:border-blue-500 flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs opacity-0 peer-checked:opacity-100"></i>
                                </div>
                            </div>
                        </label>
                    </div>



                    <!-- Users -->
                    <div class="relative">
                        <input type="checkbox" id="users" name="data_types[]" value="users" 
                               class="sr-only peer">
                        <label for="users" class="flex items-center p-4 bg-white border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-user-md text-indigo-600"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">Personnel</div>
                                    <div class="text-sm text-gray-600">Médecins et réceptionnistes</div>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:bg-blue-500 peer-checked:border-blue-500 flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs opacity-0 peer-checked:opacity-100"></i>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
                
                @error('data_types')
                <p class="mt-2 text-sm text-red-600 flex items-center space-x-1">
                    <i class="fas fa-exclamation-circle text-xs"></i>
                    <span>{{ $message }}</span>
                </p>
                @enderror
            </div>

            <div class="border-t border-gray-200"></div>

            <!-- Date Range -->
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-week text-white text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Période (Optionnelle)</h3>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                            Date de Début
                        </label>
                        <input type="date" id="start_date" name="start_date" 
                               value="{{ old('start_date') }}"
                               max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">
                            Date de Fin
                        </label>
                        <input type="date" id="end_date" name="end_date" 
                               value="{{ old('end_date') }}"
                               max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border-2 border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all duration-200 @error('end_date') border-red-500 @enderror">
                        @error('end_date')
                        <p class="mt-1 text-sm text-red-600 flex items-center space-x-1">
                            <i class="fas fa-exclamation-circle text-xs"></i>
                            <span>{{ $message }}</span>
                        </p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <strong>Note :</strong> Si aucune période n'est spécifiée, toutes les données disponibles seront exportées. 
                            Le filtrage par date s'applique aux dates de création pour la plupart des types de données (dates de rendez-vous pour les rendez-vous, dates de visite pour les dossiers médicaux, etc.).
                        </div>
                    </div>
                </div>
            </div>



            <!-- Form Actions -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 rounded-b-2xl">
                <div class="flex flex-col sm:flex-row items-center justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                    <button type="button" onclick="history.back()" 
                            class="w-full sm:w-auto bg-gradient-to-r from-gray-300 to-gray-400 hover:from-gray-400 hover:to-gray-500 text-gray-800 px-6 py-3 rounded-xl font-medium transition-all duration-200 text-center">
                        Annuler
                    </button>
                    <button type="submit" id="exportBtn"
                            class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center justify-center space-x-2">
                        <i class="fas fa-download"></i>
                        <span>Exporter les Données</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('exportForm');
    const exportBtn = document.getElementById('exportBtn');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    // Form validation
    form.addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('input[name="data_types[]"]:checked');
        
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Veuillez sélectionner au moins un type de données à exporter.');
            return false;
        }

        // Show loading state
        exportBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Export en cours...</span>
        `;
        exportBtn.disabled = true;
        exportBtn.classList.add('opacity-75', 'cursor-not-allowed');

        // Reset button after 5 seconds in case something goes wrong
        setTimeout(() => {
            exportBtn.innerHTML = `
                <i class="fas fa-download"></i>
                <span>Exporter les Données</span>
            `;
            exportBtn.disabled = false;
            exportBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        }, 5000);
    });

    // Date validation
    startDateInput.addEventListener('change', function() {
        if (this.value && endDateInput.value && this.value > endDateInput.value) {
            endDateInput.value = this.value;
        }
        endDateInput.min = this.value;
    });

    endDateInput.addEventListener('change', function() {
        if (this.value && startDateInput.value && this.value < startDateInput.value) {
            startDateInput.value = this.value;
        }
        startDateInput.max = this.value;
    });
});
</script>
@endsection