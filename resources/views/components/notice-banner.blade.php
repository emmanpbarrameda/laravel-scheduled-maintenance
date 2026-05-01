        @php($maintenanceNotice = app('maintenance')->notice())
        @if ($maintenanceNotice)
            <div
                style="position:fixed; top:0; left:0; right:0; z-index:50; width:100%; padding:12px 16px; background:#E7000B; color:white; text-align:center;">
                Scheduled maintenance on
                <strong>{{ $maintenanceNotice->starts_at->format('F jS, \a\t g:ia') }}</strong>.
                Please save your work before then.
                </span>
            </div>
        @endif