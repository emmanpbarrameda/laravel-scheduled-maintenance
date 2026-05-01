        @if(app('maintenance')->inBypassMode())
            <div style="position:fixed; top:0; left:0; right:0; z-index:50; width:100%; padding:12px 16px; background:#E38A00; color:white; text-align:center;">
                <span style="font-size:14px; font-weight:500;">
                    You are currently bypassing maintenance mode. The site is still down for other users.
                </span>
            </div>
            <div style="height:48px;"></div>
        @endif