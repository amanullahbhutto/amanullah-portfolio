@extends('layouts.admin')
@section('title', 'Program Income')
@section('page_title', 'Program Income')

@section('content')
<section class="admin-card" data-ajax-crud data-refresh-target="#admin-list-results">
    <div class="admin-card-head">
        <div>
            <h2>Program Income</h2>
        </div>

        <div class="head-search-wrap flex-grow-1">
            <form class="admin-search financial-filter wide-filter" method="GET" action="{{ route('admin.program-contributions.index') }}" data-live-search data-live-search-target="#admin-list-results">
                <div class="search-field flex-grow-1">
                    <i class="bi bi-search"></i>
                    <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Search by donor name, father name, or city...">
                    <button class="search-clear {{ blank(request('q')) ? 'd-none' : '' }}" type="button" data-live-search-clear>
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <select class="form-select filter-select program-filter-select" name="program_id">
                    <option value="">All programs</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" @selected($programId === $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="responsive-actions d-flex align-items-center gap-2">
            <div class="finance-badge" data-finance-total>Total Income: <strong>Rs. {{ number_format($total, 2) }}</strong></div>
            <button class="btn btn-accent btn-sm" data-crud-open data-modal="#contributionModal" data-store-url="{{ route('admin.program-contributions.store') }}">
                <i class="bi bi-plus-lg me-1"></i>Add Income
            </button>
        </div>
    </div>

    <div id="admin-list-results">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Program</th>
                        <th>Received From</th>
                        <th>Father Name</th>
                        <th>From Location (City)</th>
                        <th>Details</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contributions as $row)
                        <tr>
                            <td>{{ $row->contribution_date->format('M d, Y') }}</td>
                            <td>{{ $row->program->name }}</td>
                            <td><strong>{{ $row->contributor->name }}</strong></td>
                            <td>{{ $row->contributor->father_name ?: '-' }}</td>
                            <td>
                                @if($row->contributor->city)
                                    <span class="duration-pill"><i class="bi bi-geo-alt me-1"></i>{{ $row->contributor->city->name }}</span>
                                @elseif($row->contributor->from_location)
                                    <span>{{ $row->contributor->from_location }}</span>
                                @else
                                    <span class="text-muted-custom">-</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($row->details, 40) ?: '-' }}</td>
                            <td class="text-end text-success"><strong>Rs. {{ number_format($row->amount, 2) }}</strong></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon" data-crud-edit data-modal="#contributionModal" data-action="{{ route('admin.program-contributions.update', $row) }}" data-record="{{ json_encode(['program_id' => $row->program_id, 'name' => $row->contributor->name, 'father_name' => $row->contributor->father_name, 'city_id' => $row->contributor->city_id, 'from_location' => $row->contributor->from_location, 'amount' => $row->amount, 'contribution_date' => $row->contribution_date->format('Y-m-d'), 'details' => $row->details, 'reference' => $row->reference]) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.program-contributions.destroy', $row) }}" data-ajax-delete data-confirm="Delete this income record?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-icon danger"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted-custom">No income records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">
            @include('admin.partials.pagination', ['paginator' => $contributions])
        </div>
    </div>
</section>

{{-- Main Income Modal --}}
<div class="modal fade finance-modal" id="contributionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form>
                <input type="hidden" name="_method" value="PUT" data-method disabled>
                <div class="modal-header">
                    <h5 class="modal-title" data-modal-title>Add Income</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Program <span class="text-danger">*</span></label>
                            <select class="form-select" name="program_id" id="contributionProgramSelect" required>
                                <option value="">Select program</option>
                                @foreach($programs as $p)
                                    <option value="{{ $p->id }}" @selected($programId === $p->id)>{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" data-error-for="program_id"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Income Date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="contribution_date" value="{{ now()->format('Y-m-d') }}" required>
                            <div class="invalid-feedback" data-error-for="contribution_date"></div>
                        </div>
                        
                        {{-- Received From (Name) with Smart Auto-Suggest --}}
                        <div class="col-md-4 autocomplete-wrapper">
                            <label class="form-label">Received From (Name) <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" id="contributorNameInput" placeholder="e.g. Muhammad Ali" required autocomplete="off">
                            <div class="autocomplete-dropdown shadow-lg d-none" id="contributorNameSuggestions"></div>
                            <div class="invalid-feedback" data-error-for="name"></div>
                        </div>

                        {{-- Father Name with Smart Auto-Suggest --}}
                        <div class="col-md-4 autocomplete-wrapper">
                            <label class="form-label">Father Name</label>
                            <input class="form-control" name="father_name" id="contributorFatherInput" placeholder="Father name" autocomplete="off">
                            <div class="autocomplete-dropdown shadow-lg d-none" id="contributorFatherSuggestions"></div>
                            <div class="invalid-feedback" data-error-for="father_name"></div>
                        </div>

                        {{-- From Location (City) with Live Search & Plus (+) Icon --}}
                        <div class="col-md-4">
                            <label class="form-label">From Location (City)</label>
                            <div class="city-input-group">
                                <div class="searchable-select-wrapper" id="citySearchableWrapper">
                                    <select class="form-select d-none" name="city_id" id="hiddenCitySelect" data-city-select>
                                        <option value="">Select city</option>
                                        @foreach($cities as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="searchable-select-display" tabindex="0" id="citySelectDisplay">
                                        <span class="selected-text" id="citySelectDisplayText">Select city</span>
                                        <i class="bi bi-chevron-down small text-muted-custom"></i>
                                    </div>
                                    <div class="searchable-select-dropdown d-none" id="citySelectDropdown">
                                        <div class="searchable-select-search-box">
                                            <i class="bi bi-search search-icon"></i>
                                            <input type="text" class="search-input" id="citySearchInput" placeholder="Search city..." autocomplete="off">
                                        </div>
                                        <div class="searchable-select-options" id="citySelectOptions">
                                            <div class="searchable-option text-muted-custom" data-value="" data-text="Select city">
                                                <span>None / Clear</span>
                                            </div>
                                            @foreach($cities as $c)
                                                <div class="searchable-option" data-value="{{ $c->id }}" data-text="{{ $c->name }}">
                                                    <span>{{ $c->name }}</span>
                                                    @if($c->state)<small class="text-muted-custom">{{ $c->state }}</small>@endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-city-add" type="button" data-bs-toggle="modal" data-bs-target="#quickCityModal" title="Add new city">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" data-error-for="city_id"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required>
                            <div class="invalid-feedback" data-error-for="amount"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference / Receipt No.</label>
                            <input class="form-control" name="reference" placeholder="Optional reference">
                            <div class="invalid-feedback" data-error-for="reference"></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Details</label>
                            <textarea class="form-control" name="details" rows="2" placeholder="Details about this contribution"></textarea>
                            <div class="invalid-feedback" data-error-for="details"></div>
                        </div>

                        {{-- Notes field commented out / hidden per user request --}}
                        {{--
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Internal notes"></textarea>
                            <div class="invalid-feedback" data-error-for="notes"></div>
                        </div>
                        --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-theme" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit><span data-submit-label>Save Income</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Quick Add City Modal --}}
<div class="modal fade finance-modal" id="quickCityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-ajax-form data-quick-city data-store-url="{{ route('admin.cities.store') }}">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add City</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">City Name <span class="text-danger">*</span></label>
                        <input class="form-control" name="name" placeholder="e.g. Karachi, Lahore, Sukkur" required autofocus>
                        <input type="hidden" name="status" value="active">
                        <div class="invalid-feedback" data-error-for="name"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Province / State (Optional)</label>
                        <input class="form-control" name="state" placeholder="e.g. Sindh, Punjab, Balochistan, KPK">
                        <div class="invalid-feedback" data-error-for="state"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-theme" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-accent" type="submit" data-submit>Save City</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ------------------------------------------------------------------------
    // Comprehensive Muslim & Pakistani Names Dictionary in English (~500+ names & variations)
    // ------------------------------------------------------------------------
    const muslimNames = [
        // Ullah Compound Names
        "Hamadullah", "Hammadullah", "Hamad Ullah", "Hammad Ullah", "Amanullah", "Aman Ullah", 
        "Hameedullah", "Hameed Ullah", "Habibullah", "Habib Ullah", "Sanaullah", "Sana Ullah", 
        "Rahmatullah", "Rehmatullah", "Rehmat Ullah", "Inamullah", "Inam Ullah", "Saifullah", "Saif Ullah", 
        "Asadullah", "Asad Ullah", "Zakaullah", "Zaka Ullah", "Khairullah", "Khair Ullah", "Faizullah", "Faiz Ullah", 
        "Waliullah", "Wali Ullah", "Samiullah", "Sami Ullah", "Hidayatullah", "Hidayat Ullah", "Noorullah", "Noor Ullah", 
        "Matiullah", "Mati Ullah", "Roohullah", "Rooh Ullah", "Barkatullah", "Barkat Ullah", "Niamatullah", "Niamat Ullah", 
        "Khalilullah", "Khalil Ullah", "Zafarullah", "Zafar Ullah", "Nasrullah", "Nasr Ullah", "Ibadullah", "Ibad Ullah", 
        "Hayatullah", "Hayat Ullah", "Attaullah", "Atta Ullah", "Ataullah", "Fazalullah", "Fazal Ullah", "Qudratullah", 
        "Shukrullah", "Fatehullah", "Azimullah", "Azizullah", "Karimullah", "Rahimullah", "Wasiullah", "Hafizullah", 
        "Shakirullah", "Naqeebullah", "Ziaullah", "Zia Ullah", "Kaleemullah", "Kaleem Ullah", "Najibullah", "Rafiullah", 
        "Farmanullah", "Ihsanullah", "Ikramullah", "Irfanullah", "Ismatullah", "Kifayatullah", "Obaidullah", "Ubaidullah", 
        "Zabihullah",

        // Abdul Names
        "Abdullah", "Abdul Rehman", "Abdul Rahman", "Abdulrehman", "Abdulrahman", "Abdul Qadir", "Abdulqadir", 
        "Abdul Samad", "Abdul Wahab", "Abdul Majeed", "Abdul Rasheed", "Abdul Rashid", "Abdul Sattar", 
        "Abdul Ghaffar", "Abdul Razzaq", "Abdul Basit", "Abdul Hameed", "Abdul Malik", "Abdul Qayyum", 
        "Abdul Shakoor", "Abdul Aziz", "Abdul Jabbar", "Abdul Bari", "Abdul Wadood", "Abdul Ghafoor", 
        "Abdul Hakeem", "Abdul Haleem", "Abdul Kabeer", "Abdul Raheem", "Abdul Lateef", "Abdul Mannan", 
        "Abdul Mateen", "Abdul Mujeeb", "Abdul Muqtadir", "Abdul Naseer", "Abdul Quddus", "Abdul Raqeeb", 
        "Abdul Rauf", "Abdul Saboor", "Abdul Salam", "Abdul Sami", "Abdul Tawwab", "Abdul Wahid", 
        "Abdul Zahir", "Abdul Batin", "Abdul Wali", "Abdul Baqi", "Abdul Waris", "Abdul Ahad", "Abdul Moiz",

        // Din / Deen Compound Names
        "Salahuddin", "Ziauddin", "Nooruddin", "Shamsuddin", "Sirajuddin", "Jalaluddin", "Nizamuddin", 
        "Ghiasuddin", "Bahauddin", "Riazuddin", "Fakhruddin", "Sharafuddin", "Tajuddin", "Nasiruddin", 
        "Qamaruddin", "Imaduddin", "Muinuddin", "Mueenuddin", "Minhajuddin", "Husamuddin", "Saifuddin", 
        "Burhanuddin", "Kamaluddin", "Jamaluddin", "Najmuddin", "Mohiuddin", "Muhiuddin", "Zahiruddin",

        // Core Prophets & Companions
        "Muhammad", "Mohammad", "Mohammed", "Mohamad", "Ahmed", "Ahmad", "Ali", "Usman", "Uthman", 
        "Umar", "Omar", "Abu Bakr", "Abubakar", "Bilal", "Hamza", "Hassan", "Hasan", "Hussain", "Husain", 
        "Zaid", "Zubair", "Talha", "Saad", "Saeed", "Salman", "Khalid", "Tariq", "Ammar", "Yasir", "Anas", 
        "Huzaifa", "Miqdad", "Suhaib", "Muaz", "Muadh", "Abu Hurairah", "Abu Darda", "Abu Zar", "Abuzar", 
        "Jafar", "Jaffar", "Aqeel", "Abbas", "Haris", "Harris", "Hashim", "Ibrahim", "Ismail", "Ishaq", 
        "Yaqoob", "Yaqub", "Dawood", "Daud", "Suleman", "Sulaiman", "Ayub", "Ayoub", "Younus", "Yunus", 
        "Zakariya", "Yahya", "Haroon", "Musa", "Isa", "Idris", "Idrees", "Nuh", "Saleh", "Salih", "Hud", 
        "Lut", "Shuaib", "Adam", "Luqman",

        // Popular Pakistani & Muslim Names
        "Aamir", "Amir", "Aaqib", "Aqib", "Aariz", "Aashir", "Ashir", "Aasim", "Asim", "Aatif", "Atif", 
        "Aayan", "Ayan", "Abid", "Adil", "Adeel", "Adnan", "Afaq", "Afzal", "Ahsan", "Ahsen", "Aijaz", 
        "Ejaz", "Ajmal", "Akbar", "Akhtar", "Akram", "Alam", "Alamgir", "Altaf", "Aman", "Ameen", "Amin", 
        "Amjad", "Anis", "Anees", "Anwar", "Anwer", "Arbab", "Arfeen", "Arif", "Arsalan", "Arslan", 
        "Asad", "Asghar", "Ashraf", "Asif", "Aslam", "Ata", "Atta", "Athar", "Aurangzeb", "Awan", "Awwab", 
        "Ayaz", "Azad", "Azam", "Azhar", "Aziz", "Azmat", "Babar", "Baber", "Badar", "Badr", "Baqir", 
        "Barkat", "Basharat", "Bashir", "Basheer", "Behram", "Bilawal", "Burhan", "Daniyal", "Danyal", 
        "Dilawar", "Dildar", "Dilshad", "Ehsan", "Ehtesham", "Ehtisham", "Faiz", "Faizan", "Faisal", 
        "Fakhar", "Faraz", "Farhan", "Farid", "Fareed", "Farman", "Farooq", "Farrukh", "Fasih", "Fateh", 
        "Fawad", "Fayyaz", "Feroz", "Fida", "Furqan", "Ghafoor", "Ghalib", "Ghani", "Ghazanfar", "Ghulam", 
        "Gohar", "Gul", "Gulzar", "Habib", "Hafeez", "Hafiz", "Haider", "Haji", "Hakeem", "Hakim", 
        "Hamad", "Hammad", "Hamdan", "Hameed", "Hamid", "Hanif", "Haneef", "Hashir", "Hasnain", "Haziq", 
        "Hidayat", "Hilal", "Humayun", "Hunain", "Huraira", "Ibad", "Iftikhar", "Ihsan", "Ijaz", "Ikram", 
        "Ilyas", "Imad", "Imam", "Imtiaz", "Imran", "Inam", "Inayat", "Iqbal", "Iqrar", "Irfan", "Irtaza", 
        "Ishtiaq", "Islam", "Israr", "Izhar", "Jahangir", "Jalal", "Jalil", "Jamal", "Jamil", "Jameel", 
        "Jamshaid", "Jamshed", "Javed", "Javid", "Jawad", "Jibran", "Junaid", "Kabir", "Kabeer", "Kaleem", 
        "Kalim", "Kamal", "Kamran", "Karam", "Kareem", "Karim", "Kashif", "Kazim", "Khadim", "Khaleeq", 
        "Khaliq", "Khalil", "Khan", "Khawar", "Khurram", "Khursheed", "Khurshid", "Kifayat", "Kousar", 
        "Latif", "Lateef", "Liaqat", "Liaquat", "Mahad", "Mahboob", "Mahmood", "Mehmood", "Majid", "Majeed", 
        "Malik", "Mamoon", "Mansoor", "Mansur", "Manzoor", "Manzur", "Maqbool", "Maqsood", "Maroof", "Marwan", 
        "Masood", "Masroor", "Mati", "Mazhar", "Meer", "Mehdi", "Mehran", "Mian", "Mikael", "Mir", "Mirza", 
        "Misbah", "Moazzam", "Moeen", "Moin", "Mohsin", "Mokhtar", "Mubarak", "Mueen", "Mufassir", "Mughal", 
        "Muhsin", "Mujahid", "Mujtaba", "Mukarram", "Mukhtar", "Mumtaz", "Muneeb", "Muneer", "Munir", 
        "Murad", "Murshid", "Murtaza", "Musab", "Musharraf", "Mushtaq", "Mustafa", "Mustaqeem", "Mustansir", 
        "Mutahir", "Muzammil", "Muzammal", "Nabeel", "Nabil", "Nadeem", "Nadir", "Najeeb", "Najib", "Naeem", 
        "Nafees", "Naimat", "Najam", "Naqi", "Naqvi", "Naseem", "Naseer", "Nasir", "Nasim", "Nasr", 
        "Nauman", "Noman", "Naveed", "Navid", "Nawab", "Nawaz", "Nayeem", "Nazeer", "Nazir", "Niamat", 
        "Nisar", "Niyaz", "Niaz", "Nizam", "Noor", "Nouman", "Nuaim", "Numan", "Obaid", "Omair", "Owais", 
        "Parvez", "Parwaiz", "Pasha", "Peer", "Pir", "Qadeer", "Qadir", "Qais", "Qaisar", "Qamar", 
        "Qasid", "Qasim", "Qayoom", "Qayyum", "Qudrat", "Qurban", "Rabnawaz", "Raees", "Rais", "Rafi", 
        "Rafiq", "Raheem", "Rahim", "Rahman", "Raja", "Ramzan", "Rashid", "Rasheed", "Rauf", "Rawal", 
        "Rayyan", "Raza", "Razzaq", "Rehman", "Rehan", "Riaz", "Riyaz", "Rizwan", "Rohail", "Rooh", 
        "Roshan", "Rustam", "Saadat", "Saadi", "Sabir", "Sabeer", "Saboor", "Sabri", "Sadaqat", "Sadeeq", 
        "Sadid", "Sadiq", "Safdar", "Safi", "Saghair", "Sagheer", "Sajid", "Sajjad", "Sakhi", "Salah", 
        "Salam", "Salamat", "Saleem", "Salim", "Samar", "Sami", "Sameer", "Samir", "Sana", "Sanan", 
        "Saqib", "Sarfaraz", "Sarfraz", "Sardar", "Sarmad", "Sarwar", "Saud", "Sayed", "Sayyid", "Shaban", 
        "Shahbaz", "Shabbir", "Shafi", "Shafiq", "Shafqat", "Shah", "Shaharyar", "Shaheen", "Shahid", 
        "Shaheer", "Shahjahan", "Shahmir", "Shahrukh", "Shahzad", "Shahzaman", "Shahnawaz", "Shaikh", 
        "Shakeel", "Shakir", "Shamim", "Shams", "Shamshad", "Sharafat", "Sharjeel", "Sharif", "Shareef", 
        "Shaukat", "Sher", "Sheraz", "Shiraz", "Shoaib", "Shuaib", "Shuja", "Shujaat", "Siddiq", "Siddique", 
        "Sikandar", "Siraj", "Sohaib", "Sohail", "Subhan", "Sufyan", "Sultan", "Tabarak", "Tabassum", 
        "Taha", "Tahir", "Taqi", "Tarique", "Tassawur", "Taufeeq", "Taufiq", "Tauqeer", "Tayyab", "Tayyib", 
        "Tufail", "Ubaid", "Umair", "Umer", "Usama", "Osama", "Uzair", "Wajahat", "Wajid", "Wakil", 
        "Wakeel", "Waleed", "Walid", "Wali", "Waqar", "Waqas", "Waseem", "Wasim", "Wasiq", "Yameen", 
        "Yaseen", "Yasin", "Yawar", "Younas", "Younis", "Yousaf", "Yousuf", "Yusuf", "Zafar", "Zafeer", 
        "Zafir", "Zaheer", "Zahid", "Zahir", "Zain", "Zaki", "Zakir", "Zameer", "Zamir", "Zarrar", 
        "Zaryab", "Zawar", "Zayan", "Zayd", "Zeb", "Zeeshan", "Zia", "Ziyan", "Zohair", "Zubair", 
        "Zulfiqar", "Zulqarnain"
    ];

    // Helper functions for smart space-tolerant and typo-tolerant search
    function cleanStr(s) {
        return (s || '').toLowerCase().replace(/[^a-z0-9]/g, '');
    }

    function levenshteinDistance(a, b) {
        const m = a.length, n = b.length;
        const dp = Array.from({ length: m + 1 }, () => Array(n + 1).fill(0));
        for (let i = 0; i <= m; i++) dp[i][0] = i;
        for (let j = 0; j <= n; j++) dp[0][j] = j;
        for (let i = 1; i <= m; i++) {
            for (let j = 1; j <= n; j++) {
                const cost = a[i - 1] === b[j - 1] ? 0 : 1;
                dp[i][j] = Math.min(dp[i - 1][j] + 1, dp[i][j - 1] + 1, dp[i - 1][j - 1] + cost);
            }
        }
        return dp[m][n];
    }

    function calculateNameMatchScore(query, targetName) {
        const qClean = cleanStr(query);
        const tClean = cleanStr(targetName);
        if (!qClean || !tClean) return 0;

        // Exact match ignoring spaces/case (e.g. "hamadullah" === "Hamad Ullah")
        if (tClean === qClean) return 100;

        // Prefix match on normalized string (e.g. "hamad" in "hamadullah")
        if (tClean.startsWith(qClean)) return 90 - (tClean.length - qClean.length);

        // Substring match
        if (tClean.includes(qClean)) return 75;

        // Word-level prefix matching (e.g. searching "ullah" matches "Hamad Ullah")
        const words = targetName.toLowerCase().split(/[\s\-_]+/);
        for (const w of words) {
            const wClean = cleanStr(w);
            if (wClean.startsWith(qClean)) return 80;
            if (wClean.includes(qClean)) return 65;
        }

        // Fuzzy typo tolerance (handles 1-2 typo characters like "hamdullah" -> "Hamadullah")
        if (qClean.length >= 3) {
            const sub = tClean.substring(0, Math.min(tClean.length, qClean.length + 1));
            const dist = levenshteinDistance(qClean, sub);
            if (dist <= 1) return 55;
            if (dist <= 2 && qClean.length >= 5) return 40;
        }

        return 0;
    }

    // ------------------------------------------------------------------------
    // Searchable City Select Implementation
    // ------------------------------------------------------------------------
    const cityWrapper = document.getElementById('citySearchableWrapper');
    const citySelect = document.getElementById('hiddenCitySelect');
    const cityDisplay = document.getElementById('citySelectDisplay');
    const cityDisplayText = document.getElementById('citySelectDisplayText');
    const cityDropdown = document.getElementById('citySelectDropdown');
    const citySearchInput = document.getElementById('citySearchInput');
    const cityOptionsContainer = document.getElementById('citySelectOptions');

    function syncCityDisplayFromSelect() {
        if (!citySelect || !cityDisplayText) return;
        const selectedOpt = citySelect.options[citySelect.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            cityDisplayText.textContent = selectedOpt.text;
            cityDisplayText.classList.remove('text-muted-custom');
            cityDisplayText.style.color = '#ffffff';
        } else {
            cityDisplayText.textContent = 'Select city';
            cityDisplayText.classList.add('text-muted-custom');
            cityDisplayText.style.color = '';
        }
    }

    if (cityDisplay && cityDropdown) {
        cityDisplay.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = !cityDropdown.classList.contains('d-none');
            if (isOpen) {
                closeCityDropdown();
            } else {
                openCityDropdown();
            }
        });

        function openCityDropdown() {
            cityDropdown.classList.remove('d-none');
            cityDisplay.classList.add('is-open');
            if (citySearchInput) {
                citySearchInput.value = '';
                filterCityOptions('');
                setTimeout(() => citySearchInput.focus(), 50);
            }
        }

        function closeCityDropdown() {
            cityDropdown.classList.add('d-none');
            cityDisplay.classList.remove('is-open');
        }

        function filterCityOptions(query) {
            const q = query.toLowerCase().trim();
            const options = cityOptionsContainer.querySelectorAll('.searchable-option');
            options.forEach(opt => {
                const text = (opt.dataset.text || '').toLowerCase();
                if (text.includes(q) || opt.dataset.value === '') {
                    opt.style.display = 'flex';
                } else {
                    opt.style.display = 'none';
                }
            });
        }

        if (citySearchInput) {
            citySearchInput.addEventListener('input', function () {
                filterCityOptions(this.value);
            });
            citySearchInput.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        }

        cityOptionsContainer.addEventListener('click', function (e) {
            const opt = e.target.closest('.searchable-option');
            if (!opt) return;
            const val = opt.dataset.value;
            citySelect.value = val;
            citySelect.dispatchEvent(new Event('change', { bubbles: true }));
            syncCityDisplayFromSelect();
            closeCityDropdown();
        });

        document.addEventListener('click', function (e) {
            if (!cityWrapper.contains(e.target)) {
                closeCityDropdown();
            }
        });
    }

    // Observe changes on hidden city select (e.g. from CRUD edit or Quick Add)
    if (citySelect) {
        citySelect.addEventListener('change', syncCityDisplayFromSelect);
    }

    // ------------------------------------------------------------------------
    // Program Auto-Select Retention
    // ------------------------------------------------------------------------
    const modalProgramSelect = document.getElementById('contributionProgramSelect');
    const toolbarProgramFilter = document.querySelector('.financial-filter select[name="program_id"]');

    function getPreferredProgramId() {
        if (toolbarProgramFilter && toolbarProgramFilter.value) {
            return toolbarProgramFilter.value;
        }
        return window.sessionStorage?.getItem('last_active_program_id') || '{{ $programId ?: "" }}';
    }

    if (modalProgramSelect) {
        modalProgramSelect.addEventListener('change', function () {
            if (this.value) {
                window.sessionStorage?.setItem('last_active_program_id', this.value);
            }
        });
    }

    if (toolbarProgramFilter) {
        toolbarProgramFilter.addEventListener('change', function () {
            if (this.value) {
                window.sessionStorage?.setItem('last_active_program_id', this.value);
            }
        });
    }

    // Hook into Edit / Create record clicks
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('[data-crud-edit]');
        if (editBtn) {
            setTimeout(syncCityDisplayFromSelect, 50);
        }
        const createBtn = e.target.closest('[data-crud-open]');
        if (createBtn) {
            setTimeout(() => {
                if (citySelect) citySelect.value = '';
                syncCityDisplayFromSelect();

                // Auto-select active or last selected program
                const preferredProg = getPreferredProgramId();
                if (modalProgramSelect && preferredProg) {
                    modalProgramSelect.value = preferredProg;
                }
            }, 50);
        }
    });

    // ------------------------------------------------------------------------
    // Smart Autocomplete for Contributor Name & Father Name
    // ------------------------------------------------------------------------
    const nameInput = document.getElementById('contributorNameInput');
    const nameSuggestionsBox = document.getElementById('contributorNameSuggestions');
    const fatherInput = document.getElementById('contributorFatherInput');
    const fatherSuggestionsBox = document.getElementById('contributorFatherSuggestions');

    let debounceTimer = null;

    function setupNameAutocomplete(input, suggestionsBox, isFatherField = false) {
        if (!input || !suggestionsBox) return;

        input.addEventListener('input', function () {
            const val = this.value.trim();
            if (val.length < 1) {
                suggestionsBox.classList.add('d-none');
                suggestionsBox.innerHTML = '';
                return;
            }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchSuggestions(val, input, suggestionsBox, isFatherField);
            }, 120);
        });

        input.addEventListener('focus', function () {
            const val = this.value.trim();
            if (val.length >= 1 && suggestionsBox.children.length > 0) {
                suggestionsBox.classList.remove('d-none');
            }
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('d-none');
            }
        });
    }

    function fetchSuggestions(query, input, box, isFatherField) {
        // 1. Local Muslim Names matches scored and sorted
        const localMatches = [];
        const seenNames = new Set();

        for (const name of muslimNames) {
            const score = calculateNameMatchScore(query, name);
            if (score > 0 && !seenNames.has(name.toLowerCase())) {
                seenNames.add(name.toLowerCase());
                localMatches.push({ name, score });
            }
        }

        localMatches.sort((a, b) => b.score - a.score);
        const topLocalMatches = localMatches.slice(0, 8).map(m => m.name);

        // 2. Fetch Existing Donors from Database
        fetch(`/admin/program-contributions/suggest-contributors?q=${encodeURIComponent(query)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(dbContributors => {
            box.innerHTML = '';
            let hasItems = false;

            // Render existing database donors first (with Auto-Fill capability)
            if (dbContributors && dbContributors.length > 0) {
                dbContributors.forEach(c => {
                    hasItems = true;
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item';
                    
                    const nameToShow = isFatherField ? (c.father_name || c.name) : c.name;
                    const subtitle = isFatherField 
                        ? (c.name ? `Donor: ${c.name}` : '') 
                        : (c.father_name ? `S/O: ${c.father_name}` : '') + (c.city_name ? ` • ${c.city_name}` : '');

                    item.innerHTML = `
                        <div>
                            <strong>${highlightMatch(nameToShow, query)}</strong>
                            ${subtitle ? `<span class="autocomplete-subtext">${subtitle}</span>` : ''}
                        </div>
                        <span class="autocomplete-badge"><i class="bi bi-person-check me-1"></i>Saved</span>
                    `;

                    item.addEventListener('click', function () {
                        input.value = nameToShow;
                        // If selecting for Main Name field, auto-fill father and city!
                        if (!isFatherField) {
                            if (c.father_name && fatherInput && !fatherInput.value) {
                                fatherInput.value = c.father_name;
                            }
                            if (c.city_id && citySelect) {
                                citySelect.value = c.city_id;
                                syncCityDisplayFromSelect();
                            }
                        }
                        box.classList.add('d-none');
                    });
                    box.appendChild(item);
                });
            }

            // Render dictionary name suggestions
            topLocalMatches.forEach(name => {
                // Avoid exact duplicate of what's already shown
                if (dbContributors.some(c => (isFatherField ? c.father_name : c.name)?.toLowerCase() === name.toLowerCase())) {
                    return;
                }

                hasItems = true;
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.innerHTML = `
                    <div>
                        <strong>${highlightMatch(name, query)}</strong>
                    </div>
                    <span class="text-muted-custom small"><i class="bi bi-spellcheck text-accent"></i> Correct Spelling</span>
                `;

                item.addEventListener('click', function () {
                    input.value = name;
                    box.classList.add('d-none');
                });
                box.appendChild(item);
            });

            if (hasItems) {
                box.classList.remove('d-none');
            } else {
                box.classList.add('d-none');
            }
        })
        .catch(() => {
            // Fallback to local names if network error
            box.innerHTML = '';
            if (topLocalMatches.length > 0) {
                topLocalMatches.forEach(name => {
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item';
                    item.innerHTML = `<div><strong>${highlightMatch(name, query)}</strong></div><span class="text-muted-custom small"><i class="bi bi-spellcheck text-accent"></i> Spelling</span>`;
                    item.addEventListener('click', function () {
                        input.value = name;
                        box.classList.add('d-none');
                    });
                    box.appendChild(item);
                });
                box.classList.remove('d-none');
            } else {
                box.classList.add('d-none');
            }
        });
    }

    function highlightMatch(text, query) {
        if (!text || !query) return text || '';
        const idx = text.toLowerCase().indexOf(query.toLowerCase().trim());
        if (idx !== -1) {
            return text.substring(0, idx) + '<span style="color: var(--accent); font-weight: 700; text-decoration: underline;">' + text.substring(idx, idx + query.trim().length) + '</span>' + text.substring(idx + query.trim().length);
        }
        return `<span>${text}</span>`;
    }

    setupNameAutocomplete(nameInput, nameSuggestionsBox, false);
    setupNameAutocomplete(fatherInput, fatherSuggestionsBox, true);
});
</script>
@endpush
