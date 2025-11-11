<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unresolved Lost Items - {{ $organization->name }}</title>
    <style>
        @page {
            margin: 0.5in;
            size: A4;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .organization-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 10px;
        }
        
        .report-date {
            font-size: 14px;
            color: #666;
        }
        
        .contact-info {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            border-left: 4px solid #2563eb;
        }
        
        .contact-info h3 {
            margin: 0 0 10px 0;
            color: #2563eb;
            font-size: 14px;
        }
        
        .contact-info p {
            margin: 5px 0;
            font-size: 11px;
        }
        
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .item-card {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            background-color: #ffffff;
            page-break-inside: avoid;
        }
        
        .item-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 8px;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            margin-right: 12px;
        }
        
        .no-image {
            width: 60px;
            height: 60px;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #9ca3af;
            margin-right: 12px;
        }
        
        .item-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin: 0;
        }
        
        .item-details {
            font-size: 11px;
            color: #4b5563;
        }
        
        .item-details p {
            margin: 4px 0;
        }
        
        .detail-label {
            font-weight: bold;
            color: #374151;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        .no-items {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            margin: 40px 0;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .item-card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="organization-name">{{ $organization->name }}</div>
        <div class="report-title">UNRESOLVED LOST ITEMS</div>
        <div class="report-date">Generated on {{ now()->format('F d, Y \a\t g:i A') }}</div>
    </div>

    <div class="contact-info">
        <h3>📞 Contact Information</h3>
        <p><strong>Organization:</strong> {{ $organization->name }}</p>
        @if($organization->contact_email)
            <p><strong>Email:</strong> {{ $organization->contact_email }}</p>
        @endif
        @if($organization->contact_phone)
            <p><strong>Phone:</strong> {{ $organization->contact_phone }}</p>
        @endif
        @if($organization->address)
            <p><strong>Address:</strong> {{ $organization->address }}</p>
        @endif
        <p><strong>Total Unresolved Items:</strong> {{ $lostItems->count() }}</p>
    </div>

    @if($lostItems->count() > 0)
        <div class="items-grid">
            @foreach($lostItems as $item)
                <div class="item-card">
                    <div class="item-header">
                        @if(isset($item->image_base64) && $item->image_base64)
                            <img src="{{ $item->image_base64 }}" alt="{{ $item->title }}" class="item-image">
                        @else
                            <div class="no-image">No Image</div>
                        @endif
                        <div>
                            <h3 class="item-title">{{ $item->title }}</h3>
                        </div>
                    </div>
                    
                    <div class="item-details">
                        <p><span class="detail-label">Category:</span> {{ $item->category ?? 'N/A' }}</p>
                        <p><span class="detail-label">Location Lost:</span> {{ $item->location ?? 'N/A' }}</p>
                        <p><span class="detail-label">Date Lost:</span> {{ $item->date_lost ? $item->date_lost->format('F d, Y') : 'N/A' }}</p>
                        <p><span class="detail-label">Reported By:</span> {{ $item->user?->first_name }} {{ $item->user?->last_name }}</p>
                        <p><span class="detail-label">Reported On:</span> {{ $item->created_at->format('F d, Y') }}</p>
                        @if($item->description)
                            <p><span class="detail-label">Description:</span> {{ Str::limit($item->description, 100) }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-items">
            <h3>No unresolved lost items found.</h3>
            <p>All lost items have been resolved or are under review.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Lost & Found Management System.</p>
        <p>For more information or to report a found item, please contact the organization directly.</p>
    </div>

</body>
</html>
