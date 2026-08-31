<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HelpInfoBengaliManualSeeder extends Seeder
{
    /**
     * Common instruction block for all Index / List pages as requested by user:
     * 1. Add button
     * 2. Filter & Search
     * 3. Pagination
     * 4. Row hover actions (View, Edit, Delete)
     * 5. Column sorting
     * 6. Excel & Print export
     */
    protected function getCommonIndexGuide(string $modelTitle): string
    {
        return <<<HTML
<div class="card border-primary border-opacity-25 mt-3 mb-2 bg-light">
  <div class="card-body p-3">
    <h5 class="text-primary fw-bold mb-2">
      <i class="fas fa-table-list me-2"></i>{$modelTitle} তালিকা ব্যবহারের সাধারণ নিয়মাবলী:
    </h5>
    <ul class="mb-0 lh-lg text-dark small">
      <li><strong>১. নতুন এন্ট্রি যুক্ত করা (Add Button):</strong> নতুন তথ্য যুক্ত করতে পেজের উপরে ডানপাশে থাকা <code>+ Add {$modelTitle}</code> বাটনে ক্লিক করুন।</li>
      <li><strong>২. অনুসন্ধান ও ফিল্টারিং (Filter & Search):</strong> নির্দিষ্ট তথ্য দ্রুত খুঁজে পেতে ফিল্টার ড্রপডাউন এবং সার্চ বক্স ব্যবহার করুন। ফিল্টার রিসেট করতে ফিল্ড খালি করে এন্টার চাপুন বা Reset চাপুন।</li>
      <li><strong>৩. পেজিনেশন (Pagination):</strong> তালিকার নিচে থাকা ড্রপডাউন থেকে প্রতি পেজে কতটি রেকর্ড দেখতে চান (যেমন ১০, ২০, ৫০ বা ১০০) তা নির্বাচন করুন এবং পরবর্তী পেজে যাওয়ার জন্য পেজ নম্বরে ক্লিক করুন।</li>
      <li><strong>৪. মাউস হোভার অ্যাকশন (Row Actions):</strong> টেবিলের যে কোনো সারির (Row) উপর মাউস বা কার্সর নিলে ডানপাশে স্বয়ংক্রিয়ভাবে <span class="badge bg-info text-dark"><i class="fas fa-eye"></i> View</span>, <span class="badge bg-warning text-dark"><i class="fas fa-edit"></i> Edit</span> এবং <span class="badge bg-danger"><i class="fas fa-trash"></i> Delete</span> বাটন ভেসে উঠবে।</li>
      <li><strong>৫. কলাম সর্টিং (Sorting):</strong> টেবিল হেডারের যে কোনো কলামের নামের উপর ক্লিক করে ছোট থেকে বড় (A-Z / ১-৯) বা বড় থেকে ছোট ক্রমে ডাটা সাজাতে পারেন।</li>
      <li><strong>৬. প্রিন্ট ও এক্সপোর্ট:</strong> পেজের উপরে থাকা Print বা Excel বাটনে ক্লিক করে বর্তমান ফিল্টারকৃত সম্পূর্ণ তালিকা ডাউনলোড বা সরাসরি প্রিন্ট করতে পারেন।</li>
    </ul>
  </div>
</div>
HTML;
    }

    /**
     * Common instruction block for all Report pages:
     */
    protected function getCommonReportGuide(string $reportTitle): string
    {
        return <<<HTML
<div class="card border-info border-opacity-25 mt-3 mb-2 bg-light">
  <div class="card-body p-3">
    <h5 class="text-info fw-bold mb-2">
      <i class="fas fa-chart-line me-2"></i>{$reportTitle} ব্যবহারের সাধারণ নির্দেশিকা:
    </h5>
    <ul class="mb-0 lh-lg text-dark small">
      <li><strong>১. দ্রুত তারিখ নির্বাচন (Quick Date Presets):</strong> Today, Yesterday, Last 7 Days, This Month, Last Month, This Year বাটনে এক ক্লিকেই নির্দিষ্ট মেয়াদের ডাটা ফিল্টার করুন।</li>
      <li><strong>২. তারিখ ফিল্টার (Date Range):</strong> নির্দিষ্ট সময়সীমার হিসাব দেখতে From Date এবং To Date নির্বাচন করে Search বাটনে চাপুন।</li>
      <li><strong>৩. এক্সেল ও প্রিন্ট (Excel & Print):</strong> পুরো রিপোর্টটি স্প্রেডশিটে ডাউনলোড করতে <code>Excel</code> আইকনে অথবা সরাসরি প্রিন্ট নিতে <code>Print</code> বাটনে ক্লিক করুন।</li>
      <li><strong>৪. কেপিআই ও সামারি (Summary KPIs):</strong> তালিকার উপরে ও নিচে থাকা কার্ডগুলোতে মোট কোয়ান্টিটি, মোট বিক্রয়/ব্যয়, পরিশোধ ও বকেয়ার সামগ্রিক হিসাব প্রদর্শন করা হয়।</li>
    </ul>
  </div>
</div>
HTML;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $manuals = [
            // =========================================================================
            // 1. ITEM MODULE
            // =========================================================================
            [
                'model_name' => 'Item',
                'page_type' => 'create',
                'sorting' => 1,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-box-open me-2"></i>পণ্য তৈরি ও সম্পাদনা সহায়িকা (Item Create / Edit Guide)</h4>
  <p class="text-muted">এই পেজে আপনার দোকানের নতুন পণ্য যুক্ত করতে পারবেন অথবা বিদ্যমান পণ্যের তথ্য, মূল্য ও ভ্যারিয়েন্ট আপডেট করতে পারবেন।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-list-check text-success me-2"></i>ধাপে ধাপে পণ্য এন্ট্রি করার নিয়মাবলী:</h5>
  <ol class="lh-base">
    <li><strong>ক্যাটাগরি ও ব্র্যান্ড নির্বাচন:</strong> প্রথমে পণ্যের সঠিক <code>Category</code> সিলেক্ট করুন। ক্যাটাগরি সিলেক্ট করলে স্বয়ংক্রিয়ভাবে শুধুমাত্র সেই ক্যাটাগরির অন্তর্ভুক্ত <code>Brand</code> তালিকা চলে আসবে।</li>
    <li><strong>পণ্যের নাম ও বারকোড:</strong> <code>Title</code> ফিল্ডে পণ্যের স্পষ্ট নাম লিখুন। <code>Barcode</code> ফিল্ডটি খালি রাখলে সিস্টেম স্বয়ংক্রিয়ভাবে একটি ইউনিক বারকোড জেনারেট করবে। আপনার পণ্যের গায়ে যদি প্রস্তুতকারকের নিজস্ব বারকোড থাকে, তবে বারকোড স্ক্যানার দিয়ে তা স্ক্যান করে বসাতে পারেন।</li>
    <li><strong>একক (Unit) ও মূল্য:</strong> পণ্যটি বিক্রির একক (যেমন: Pcs, Box, Kg ইত্যাদি) নির্বাচন করুন। পণ্যের আনুমানিক সাধারণ ক্রয়মূল্য ও বিক্রয়মূল্য উল্লেখ করুন।</li>
    <li><strong>ওয়ারেন্টি ও গ্যারান্টি তথ্য (ইলেকট্রনিক্স শপ):</strong> আপনার দোকানটি যদি ইলেকট্রনিক্স শপ হয় বা পণ্যটিতে ওয়ারেন্টি সুবিধা থাকে, তবে <code>Warranty Type</code> (Warranty / Guarantee) সিলেক্ট করুন এবং মেয়াদ (যেমন: 1 Year, 6 Months) উল্লেখ করুন।</li>
    <li><strong>কালার ও সাইজ ভ্যারিয়েন্ট স্টক ম্যাট্রিক্স:</strong>
      <ul>
        <li>একই পণ্যের বিভিন্ন কালার বা সাইজ থাকলে <strong>Add Variant Row</strong> বাটনে ক্লিক করে নতুন সারি যোগ করুন।</li>
        <li>প্রতিটি ভ্যারিয়েন্টের জন্য আলাদা ক্রয়মূল্য, বিক্রয়মূল্য এবং প্রাথমিক স্টক (Qty) বসান।</li>
        <li><em>নোট: ইলেকট্রনিক্স শপের ক্ষেত্রে সাইজ প্রযোজ্য না থাকায় স্বয়ংক্রিয়ভাবে শুধু কালার ও মূল্য ভিত্তিক ম্যাট্রিক্স প্রদর্শিত হবে।</em></li>
      </ul>
    </li>
    <li><strong>ছবি ও সংরক্ষণ:</strong> পণ্যের আকর্ষণীয় ছবি আপলোড করুন এবং সবশেষে <strong>Submit / Save</strong> বাটনে ক্লিক করুন।</li>
  </ol>

  <h5 class="text-dark mt-3"><i class="fas fa-question-circle text-warning me-2"></i>সাধারণ প্রশ্ন ও সমাধান (FAQ):</h5>
  <div class="border rounded p-2 mb-2 bg-light">
    <p class="mb-1 text-primary fw-bold">প্রশ্ন: বারকোড ফিল্ডে কি আমি নিজে কিছু লিখব নাকি খালি রাখব?</p>
    <p class="mb-0 text-secondary">উত্তর: আপনার পণ্যের গায়ে যদি আগে থেকেই কোনো বারকোড প্রিন্ট করা থাকে, তবে স্ক্যানার দিয়ে তা বসিয়ে দিন। আর যদি কোনো বারকোড না থাকে, তবে ফিল্ডটি খালি রাখুন; সিস্টেম স্বয়ংক্রিয়ভাবে একটি নতুন বারকোড ও ইমেজ তৈরি করে দেবে।</p>
  </div>
  <div class="border rounded p-2 mb-2 bg-light">
    <p class="mb-1 text-primary fw-bold">প্রশ্ন: ইলেকট্রনিক্স শপে সাইজ কলাম দেখতে পাচ্ছি না কেন?</p>
    <p class="mb-0 text-secondary">উত্তর: সাইট সেটিংসে শপ টাইপ <code>Electronics</code> করা থাকলে কাপড়ের সাইজের প্রয়োজন না থাকায় সিস্টেম স্বয়ংক্রিয়ভাবে সাইজ অপশন হাইড করে রাখে যাতে সময় বাঁচে।</p>
  </div>
  <div class="border rounded p-2 mb-2 bg-light">
    <p class="mb-1 text-primary fw-bold">প্রশ্ন: পণ্যের একাধিক রঙে কি আলাদা আলাদা বিক্রয়মূল্য রাখা যাবে?</p>
    <p class="mb-0 text-secondary">উত্তর: হ্যাঁ! নিচের ভ্যারিয়েন্ট ম্যাট্রিক্সে প্রতিটি কালারের জন্য আলাদা ক্রয়মূল্য ও বিক্রয়মূল্য নির্ধারণ করতে পারবেন।</p>
  </div>
</div>',
            ],
            [
                'model_name' => 'Item',
                'page_type' => 'index',
                'sorting' => 2,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-boxes me-2"></i>পণ্য তালিকা সহায়িকা (Item List Guide)</h4>
  <p class="text-muted">দোকানের সকল পণ্যের বর্তমান স্টক, ক্রয়মূল্য, বিক্রয়মূল্য এবং স্ট্যাটাস এই তালিকা থেকে পর্যবেক্ষণ ও নিয়ন্ত্রণ করা যায়।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-filter text-primary me-2"></i>উন্নত ফিল্টারিং ও অনুসন্ধান:</h5>
  <ul class="lh-base">
    <li><strong>ক্যাটাগরি ও ব্র্যান্ড ফিল্টার:</strong> নির্দিষ্ট ক্যাটাগরি বা ব্র্যান্ডের পণ্যগুলো এক ক্লিকে আলাদা করে দেখতে পারবেন।</li>
    <li><strong>কালার ও সাইজ ফিল্টার:</strong> নির্দিষ্ট কোনো কালার বা সাইজের পণ্য কতটুকু স্টকে আছে তা দ্রুত খুঁজে বের করতে পারবেন।</li>
    <li><strong>বারকোড / নাম দিয়ে দ্রুত সার্চ:</strong> সার্চ বক্সে পণ্যের নাম বা বারকোড টাইপ করলে তৎক্ষণাৎ কাঙ্ক্ষিত পণ্য ফিল্টার হয়ে আসবে।</li>
    <li><strong>বারকোড প্রিন্ট:</strong> তালিকার যে কোনো পণ্যের ভিউ পেজে গিয়ে ওয়ান ক্লিকে নির্দিষ্ট সংখ্যক বারকোড স্টিকার প্রিন্ট করতে পারবেন।</li>
  </ul>
  ' . $this->getCommonIndexGuide('পণ্য (Item)') . '
</div>',
            ],
            [
                'model_name' => 'Item',
                'page_type' => 'view',
                'sorting' => 3,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-eye me-2"></i>পণ্যের বিস্তারিত বিবরণী ও বারকোড প্রিন্ট (Item Details Guide)</h4>
  <p class="text-muted">এই পেজে পণ্যের বারকোড প্রিভিউ, ভ্যারিয়েন্ট ভিত্তিক বর্তমান স্টক ও ওয়ারেন্টি পলিসি দেখতে পারবেন।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-barcode text-dark me-2"></i>বারকোড স্টিকার প্রিন্ট করার নিয়ম:</h5>
  <ol class="lh-base">
    <li>ডানপাশে থাকা <strong>Print Barcode</strong> অপশনে আপনার প্রয়োজনীয় স্টিকার সংখ্যা (যেমন: 10, 20 বা 50) লিখুন।</li>
    <li>লেবেল সাইজ নিশ্চিত করুন (সাইট সেটিংস অনুযায়ী 4x2 ইত্যাদি অটো কনফিগার করা থাকে)।</li>
    <li><strong>Print</strong> বাটনে ক্লিক করলে আপনার বারকোড প্রিন্টারে সরাসরি স্টিকার প্রিন্ট শুরু হবে।</li>
  </ol>
</div>',
            ],

            // =========================================================================
            // 2. BRAND MODULE
            // =========================================================================
            [
                'model_name' => 'Brand',
                'page_type' => 'create',
                'sorting' => 4,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-copyright me-2"></i>ব্র্যান্ড তৈরি ও সম্পাদনা সহায়িকা (Brand Setup Guide)</h4>
  <p class="text-muted">আপনার পণ্যের ব্র্যান্ড বা প্রস্তুতকারক প্রতিষ্ঠানের তথ্য এখানে সংরক্ষণ করুন।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-list-check text-success me-2"></i>ব্র্যান্ড এন্ট্রি নির্দেশিকা:</h5>
  <ul class="lh-base">
    <li><strong>Category:</strong> ব্র্যান্ডটি কোন ক্যাটাগরির অন্তর্ভুক্ত (যেমন: Mobile, Laptop, Men Fashion) তা নির্বাচন করুন। এতে পণ্য তৈরির সময় ক্যাটাগরি সিলেক্ট করলে সঠিক ব্র্যান্ড অটো চলে আসবে।</li>
    <li><strong>Brand Name:</strong> ব্র্যান্ডের নাম লিখুন (যেমন: Samsung, Apple, Walton, Apex ইত্যাদি)।</li>
    <li><strong>Sorting Order:</strong> ড্রপডাউনে এই ব্র্যান্ডটি কত নম্বরে দেখতে চান সেই ক্রমিক সংখ্যা দিন।</li>
    <li><strong>Status:</strong> ব্র্যান্ডটি চালু রাখতে <code>Active</code> রাখুন।</li>
  </ul>
</div>',
            ],
            [
                'model_name' => 'Brand',
                'page_type' => 'index',
                'sorting' => 5,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-tags me-2"></i>ব্র্যান্ড তালিকা সহায়িকা (Brand List Guide)</h4>
  <p class="text-muted">সকল ব্র্যান্ডের তালিকা, সংশ্লিষ্ট ক্যাটাগরি এবং স্ট্যাটাস এখান থেকে পরিচালনা করা যায়।</p>
  ' . $this->getCommonIndexGuide('ব্র্যান্ড (Brand)') . '
</div>',
            ],

            // =========================================================================
            // 3. CATEGORY MODULE
            // =========================================================================
            [
                'model_name' => 'Category',
                'page_type' => 'create',
                'sorting' => 6,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-folder-plus me-2"></i>ক্যাটাগরি তৈরি সহায়িকা (Category Setup Guide)</h4>
  <p class="text-muted">পণ্যসমূহকে সুন্দরভাবে বিন্যস্ত করার জন্য ক্যাটাগরি তৈরি করুন।</p>
  <ul class="lh-base">
    <li><strong>Title:</strong> ক্যাটাগরির নাম দিন (যেমন: Electronics, Clothing, Grocery ইত্যাদি)।</li>
    <li><strong>Parent Category:</strong> সাব-ক্যাটাগরি তৈরি করতে মূল প্যারেন্ট ক্যাটাগরি সিলেক্ট করুন।</li>
    <li><strong>Module:</strong> সাধারণত <code>Item</code> সিলেক্ট রাখতে হবে।</li>
  </ul>
</div>',
            ],
            [
                'model_name' => 'Category',
                'page_type' => 'index',
                'sorting' => 7,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-folder-open me-2"></i>ক্যাটাগরি তালিকা সহায়িকা (Category List Guide)</h4>
  <p class="text-muted">দোকানের সকল পণ্য ক্যাটাগরির তালিকা এখান থেকে সক্রিয়, নিষ্ক্রিয় বা সম্পাদনা করুন।</p>
  ' . $this->getCommonIndexGuide('ক্যাটাগরি (Category)') . '
</div>',
            ],

            // =========================================================================
            // 4. COLOR, SIZE, UNIT MODULES
            // =========================================================================
            [
                'model_name' => 'Color',
                'page_type' => 'create',
                'sorting' => 8,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-palette me-2"></i>রঙ তৈরি সহায়িকা (Color Setup Guide)</h4>
  <p class="text-muted">পণ্যের রঙের বৈচিত্র্য সংরক্ষণের জন্য কালার তৈরি করুন।</p>
  <ul class="lh-base">
    <li><strong>Title:</strong> রঙের নাম লিখুন (যেমন: Black, Midnight Blue, Silver ইত্যাদি)।</li>
    <li><strong>Color Code:</strong> কালার পিকার থেকে হেক্স কোড (যেমন: <code>#000000</code>) সিলেক্ট করুন।</li>
  </ul>
</div>',
            ],
            [
                'model_name' => 'Color',
                'page_type' => 'index',
                'sorting' => 9,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-palette me-2"></i>রঙ তালিকা সহায়িকা (Color List Guide)</h4>
  <p class="text-muted">সিস্টেমে সংরক্ষিত সকল রঙের নাম ও কালার কোড প্রিভিউ তালিকা।</p>
  ' . $this->getCommonIndexGuide('রং (Color)') . '
</div>',
            ],
            [
                'model_name' => 'Size',
                'page_type' => 'create',
                'sorting' => 10,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-ruler me-2"></i>সাইজ তৈরি সহায়িকা (Size Setup Guide)</h4>
  <p class="text-muted">পোশাক বা সাইজ-ভিত্তিক পণ্যের পরিমাপ সংরক্ষণের নিয়ম।</p>
  <ul class="lh-base">
    <li><strong>Title:</strong> সাইজের নাম লিখুন (যেমন: S, M, L, XL, XXL, 32, 34 ইত্যাদি)।</li>
    <li><em>নোট: ইলেকট্রনিক্স শপের ক্ষেত্রে সাইজ ফিল্ড স্বয়ংক্রিয়ভাবে লুকানো থাকে।</em></li>
  </ul>
</div>',
            ],
            [
                'model_name' => 'Size',
                'page_type' => 'index',
                'sorting' => 11,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-ruler-combined me-2"></i>সাইজ তালিকা সহায়িকা (Size List Guide)</h4>
  <p class="text-muted">পোশাকের সাইজের তালিকা পরিচালনা ও সর্টিং ক্রম নির্ধারণের পেজ।</p>
  ' . $this->getCommonIndexGuide('সাইজ (Size)') . '
</div>',
            ],
            [
                'model_name' => 'Unit',
                'page_type' => 'create',
                'sorting' => 12,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-balance-scale me-2"></i>একক তৈরি সহায়িকা (Unit Setup Guide)</h4>
  <p class="text-muted">পণ্য বিক্রয় ও ক্রয় পরিমাপের একক তৈরি করুন।</p>
  <ul class="lh-base">
    <li><strong>Title:</strong> এককের নাম লিখুন (যেমন: Pcs, Box, Kg, Liter, Meter ইত্যাদি)।</li>
  </ul>
</div>',
            ],
            [
                'model_name' => 'Unit',
                'page_type' => 'index',
                'sorting' => 13,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-balance-scale-right me-2"></i>পরিমাপের একক তালিকা (Unit List Guide)</h4>
  <p class="text-muted">পণ্য বিক্রয় ও মজুদের একক তালিকা ব্যবস্থাপনা।</p>
  ' . $this->getCommonIndexGuide('একক (Unit)') . '
</div>',
            ],

            // =========================================================================
            // 5. PURCHASE / STOCK IN MODULE
            // =========================================================================
            [
                'model_name' => 'Purchase',
                'page_type' => 'create',
                'sorting' => 14,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-truck-loading me-2"></i>মালামাল ক্রয় ও স্টক-ইন সহায়িকা (Purchase / Stock-In Guide)</h4>
  <p class="text-muted">সরবরাহকারীর কাছ থেকে মালামাল গ্রহণ, মূল্য নির্ধারণ এবং সিরিয়াল নম্বর এন্ট্রির বিস্তারিত নিয়ম।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-list-check text-success me-2"></i>ক্রয় চালান তৈরির ধাপসমূহ:</h5>
  <ol class="lh-base">
    <li><strong>সরবরাহকারী ও তারিখ:</strong> <code>Supplier</code> ড্রপডাউন থেকে ভেন্ডর সিলেক্ট করুন এবং চালানের তারিখ ও চালান নম্বর উল্লেখ করুন।</li>
    <li><strong>পণ্য নির্বাচন ও স্টক যোগ:</strong> পণ্য সার্চ করে সিলেক্ট করুন। কালার ও সাইজ ভ্যারিয়েন্ট অনুযায়ী মোট পরিমাণ (Qty) এবং ইউনিট ক্রয়মূল্য দিন।</li>
    <li><strong>সিরিয়াল নম্বর / IMEI ইনপুট (ইলেকট্রনিক্স পণ্য):</strong>
      <ul>
        <li>পণ্যটিতে সিরিয়াল ট্র্যাকিং থাকলে সিরিয়াল ইনপুট বক্সে প্রতিটি সিরিয়াল কমা (,) অথবা নতুন লাইনে লিখে বা স্ক্যানার দিয়ে পরপর স্ক্যান করুন।</li>
        <li>মোট পণ্যের পরিমাণের সাথে স্ক্যানকৃত সিরিয়াল সংখ্যা স্বয়ংক্রিয়ভাবে মিল হতে হবে।</li>
      </ul>
    </li>
    <li><strong>মূল্য ও পরিশোধ:</strong> মোট বিলের বিপরীতে যদি তাৎক্ষণিক কোনো টাকা পরিশোধ করা হয়, তবে <code>Paid Amount</code> ঘরে বসান এবং পেমেন্ট মেথড (Cash/Bank) নির্বাচন করুন। অবশিষ্ট টাকা স্বয়ংক্রিয়ভাবে সরবরাহকারীর বকেয়া হিসেবে সংরক্ষিত হবে।</li>
  </ol>
</div>',
            ],
            [
                'model_name' => 'Purchase',
                'page_type' => 'index',
                'sorting' => 15,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-receipt me-2"></i>ক্রয় চালানের তালিকা (Purchase Invoices Guide)</h4>
  <p class="text-muted">সকল ক্রয় চালান, মোট টাকার পরিমাণ, পরিশোধিত টাকা ও বকেয়া পাওনার হিসাব।</p>
  ' . $this->getCommonIndexGuide('ক্রয় চালান (Purchase)') . '
</div>',
            ],
            [
                'model_name' => 'Purchase',
                'page_type' => 'view',
                'sorting' => 16,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-file-invoice-dollar me-2"></i>ক্রয় চালানের বিস্তারিত বিল ও সিরিয়াল তালিকা</h4>
  <p class="text-muted">চালানের আওতায় কী কী পণ্য ও সিরিয়াল ইন হয়েছে তার বিস্তারিত ভিউ এবং প্রিন্ট কপি।</p>
</div>',
            ],

            // =========================================================================
            // 6. POS / TERMINAL MODULE
            // =========================================================================
            [
                'model_name' => 'Pos',
                'page_type' => 'index',
                'sorting' => 17,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-cash-register me-2"></i>ক্যাশ কাউন্টার / পিওএস টার্মিনাল সহায়িকা (POS Guide)</h4>
  <p class="text-muted">ক্যাশ কাউন্টারে দ্রুত বারকোড স্ক্যান, কাস্টমার নির্বাচন ও বিল প্রিন্ট করার গাইড।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-keyboard text-info me-2"></i>জরুরি কিবোর্ড শর্টকাট:</h5>
  <table class="table table-bordered table-sm small mb-3">
    <thead class="table-light">
      <tr><th>শর্টকাট কী</th><th>কাজ</th></tr>
    </thead>
    <tbody>
      <tr><td><code>F2</code></td><td>বারকোড সার্চ বক্সে কার্সর নেওয়া (দ্রুত স্ক্যানিংয়ের জন্য)</td></tr>
      <tr><td><code>F4</code></td><td>কাস্টমার সার্চ ফিল্ডে কার্সর নেওয়া</td></tr>
      <tr><td><code>Enter</code> (পেমেন্ট বক্সে)</td><td>ইনভয়েস সম্পন্ন করা ও রিসিপ্ট প্রিন্ট নেওয়া</td></tr>
    </tbody>
  </table>

  <h5 class="text-dark mt-3"><i class="fas fa-check-double text-success me-2"></i>পণ্য বিক্রয়ের সহজ ধাপ:</h5>
  <ol class="lh-base">
    <li>বারকোড স্ক্যানার দিয়ে পণ্যের গায়ে স্ক্যান করুন; পণ্য স্বয়ংক্রিয়ভাবে কার্টে যোগ হবে।</li>
    <li>ইলেকট্রনিক্স পণ্যের ক্ষেত্রে পপআপ থেকে গ্রাহকের নির্দিষ্ট <strong>সিরিয়াল নম্বর</strong> সিলেক্ট করুন।</li>
    <li>গ্রাহকের নাম বা মোবাইল নম্বর দিন (নতুন গ্রাহক হলে সঙ্গে সঙ্গে যুক্ত করতে পারেন)।</li>
    <li>প্রয়োজনে ছাড় (Discount) বা লয়্যালটি পয়েন্ট ব্যবহার করুন।</li>
    <li>ক্যাশ বা কার্ডে প্রাপ্ত টাকার পরিমাণ লিখুন এবং <strong>Complete Sale & Print</strong> বাটনে চাপুন।</li>
  </ol>
</div>',
            ],

            // =========================================================================
            // 7. INVOICE MODULE
            // =========================================================================
            [
                'model_name' => 'Invoice',
                'page_type' => 'index',
                'sorting' => 18,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-file-invoice me-2"></i>বিক্রয় ইনভয়েস তালিকা সহায়িকা (Sales Invoices Guide)</h4>
  <p class="text-muted">দোকানের সকল বিক্রয় ইনভয়েস, কাস্টমার বকেয়া এবং বিল প্রিন্ট সংক্রান্ত তথ্য।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-sliders-h text-primary me-2"></i>ইনভয়েস ফিল্টারিং সুবিধা:</h5>
  <ul class="lh-base">
    <li>ইনভয়েস নম্বর বা কাস্টমারের মোবাইল নম্বর দিয়ে সার্চ করতে পারেন।</li>
    <li><strong>Advance Filter</strong> আইকনে ক্লিক করে নির্দিষ্ট তারিখের পরিসীমা (From Date - To Date) এবং পেমেন্ট স্ট্যাটাস (Paid / Due) অনুযায়ী ফিল্টার করতে পারেন।</li>
    <li>তালিকার Action বাটন থেকে যে কোনো বিলের ডুপ্লিকেট থার্মাল বা A4 ইনভয়েস রি-প্রিন্ট নিতে পারেন।</li>
  </ul>
  ' . $this->getCommonIndexGuide('বিক্রয় ইনভয়েস (Invoice)') . '
</div>',
            ],
            [
                'model_name' => 'Invoice',
                'page_type' => 'view',
                'sorting' => 19,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-print me-2"></i>ইনভয়েস প্রিভিউ ও সেলস রিটার্ন</h4>
  <p class="text-muted">গ্রাহকের পূর্ণাঙ্গ চালান দেখা, থার্মাল ও এ৪ সাইজে প্রিন্ট নেওয়া এবং সেলস রিটার্ন প্রসেস করা যায়।</p>
</div>',
            ],

            // =========================================================================
            // 8. WASTAGE / DAMAGE MODULE
            // =========================================================================
            [
                'model_name' => 'Wastage',
                'page_type' => 'create',
                'sorting' => 20,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-dumpster-fire me-2"></i>নষ্ট বা অপচয় পণ্য এন্ট্রি সহায়িকা (Wastage Entry Guide)</h4>
  <p class="text-muted">দোকানের কোনো পণ্য নষ্ট, মেয়াদোত্তীর্ণ বা ক্ষতিগ্রস্ত হলে স্টক থেকে বাদ দেওয়ার নিয়ম।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-list-check text-success me-2"></i>এন্ট্রি করার নিয়মাবলী:</h5>
  <ol class="lh-base">
    <li>অপচয়ের ধরন নির্বাচন করুন: <code>Damaged</code> (ভাঙা/নষ্ট), <code>Expired</code> (মেয়াদ শেষ), <code>Lost</code> (হারিয়ে যাওয়া) ইত্যাদি।</li>
    <li>পণ্য সার্চ করে নষ্ট হওয়া পণ্যের পরিমাণ লিখুন। পণ্যটিতে সিরিয়াল থাকলে নির্দিষ্ট সিরিয়ালটি সিলেক্ট করুন।</li>
    <li>সংরক্ষণ করলে চালানটি <strong>Pending</strong> অবস্থায় থাকবে। অ্যাডমিন অ্যাপ্রুভ করলে তবেই স্টক থেকে স্বয়ংক্রিয়ভাবে বাদ হবে।</li>
  </ol>
</div>',
            ],
            [
                'model_name' => 'Wastage',
                'page_type' => 'index',
                'sorting' => 21,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-trash-alt me-2"></i>অপচয় পণ্যের তালিকা সহায়িকা (Wastage List Guide)</h4>
  <p class="text-muted">ক্ষতিগ্রস্ত পণ্যের চালানের স্থিতি (Pending / Approved) এবং আর্থিক ক্ষতির পরিমাণ দেখার পেজ।</p>
  ' . $this->getCommonIndexGuide('অপচয় চালান (Wastage)') . '
</div>',
            ],
            [
                'model_name' => 'Wastage',
                'page_type' => 'view',
                'sorting' => 22,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-clipboard-check me-2"></i>অপচয় চালান অনুমোদন ও বিবরণী</h4>
  <p class="text-muted">অপচয়ের বিবরণ পর্যবেক্ষণ করে চালান অনুমোদন (Approve) বা বাতিল (Reject) করুন।</p>
</div>',
            ],

            // =========================================================================
            // 9. WARRANTY CLAIM MODULE
            // =========================================================================
            [
                'model_name' => 'WarrantyClaim',
                'page_type' => 'create',
                'sorting' => 23,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-tools me-2"></i>ওয়ারেন্টি ক্লেইম গ্রহণ সহায়িকা (Warranty Claim Entry)</h4>
  <p class="text-muted">গ্রাহকের কোনো পণ্যে সমস্যা দেখা দিলে ওয়ারেন্টি বা গ্যারান্টি সার্ভিসের জন্য গ্রহণ করার নিয়ম।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-list-check text-success me-2"></i>ক্লেইম রেজিস্ট্রেশনের ধাপ:</h5>
  <ol class="lh-base">
    <li><strong>সিরিয়াল / ইনভয়েস ভেরিফিকেশন:</strong> সিরিয়াল নম্বর বা ইনভয়েস নম্বর লিখুন। সিস্টেম স্বয়ংক্রিয়ভাবে বিক্রির তারিখ ও ওয়ারেন্টির মেয়াদ সক্রিয় আছে কি না তা যাচাই করবে।</li>
    <li><strong>সমস্যার বিবরণ:</strong> গ্রাহকের পণ্যে কী সমস্যা হচ্ছে (যেমন: ডিসপ্লে কাজ করে না, সাউন্ড নেই) তা স্পষ্ট করে লিখুন।</li>
    <li><strong>ক্লেইম টাইপ:</strong> <code>Warranty</code> (মেরামত সার্ভিস) নাকি <code>Guarantee</code> (নতুন পণ্য দিয়ে পরিবর্তন) তা নির্বাচন করুন।</li>
    <li>টোকেন প্রিন্ট করে গ্রাহককে প্রদান করুন।</li>
  </ol>
</div>',
            ],
            [
                'model_name' => 'WarrantyClaim',
                'page_type' => 'index',
                'sorting' => 24,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-clipboard-list me-2"></i>ওয়ারেন্টি ক্লেইম ট্র্যাকিং তালিকা (Warranty Claims List)</h4>
  <p class="text-muted">সার্ভিসে থাকা পণ্যের বর্তমান অবস্থা (Received, Sent to Vendor, Repaired, Delivered) ট্র্যাক করুন।</p>
  ' . $this->getCommonIndexGuide('ওয়ারেন্টি ক্লেইম (Warranty Claim)') . '
</div>',
            ],

            // =========================================================================
            // 10. CLIENT & SUPPLIER MODULES
            // =========================================================================
            [
                'model_name' => 'Client',
                'page_type' => 'create',
                'sorting' => 25,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-user-plus me-2"></i>গ্রাহক প্রোফাইল তৈরি (Client Setup Guide)</h4>
  <p class="text-muted">গ্রাহকের নাম, মোবাইল নম্বর, ঠিকানা, পূর্বের বকেয়া ব্যালেন্স এবং ক্রেডিট লিমিট নির্ধারণ করুন।</p>
</div>',
            ],
            [
                'model_name' => 'Client',
                'page_type' => 'index',
                'sorting' => 26,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-users me-2"></i>গ্রাহক তালিকা ও খতিয়ান (Clients List Guide)</h4>
  <p class="text-muted">সকল গ্রাহকের মোট ক্রয়, মোট পরিশোধ, বকেয়া ব্যালেন্স এবং ব্যক্তিগত লেজার স্টেটমেন্ট।</p>
  ' . $this->getCommonIndexGuide('গ্রাহক (Client)') . '
</div>',
            ],
            [
                'model_name' => 'Supplier',
                'page_type' => 'create',
                'sorting' => 27,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-dolly me-2"></i>সরবরাহকারী প্রোফাইল তৈরি (Supplier Setup Guide)</h4>
  <p class="text-muted">মালামাল সরবরাহকারী প্রতিষ্ঠান বা ভেন্ডরের যোগাযোগের তথ্য ও পূর্বের পাওনা ব্যালেন্স এন্ট্রি।</p>
</div>',
            ],
            [
                'model_name' => 'Supplier',
                'page_type' => 'index',
                'sorting' => 28,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-truck me-2"></i>সরবরাহকারী তালিকা ও পাওনা ব্যালেন্স (Supplier List)</h4>
  <p class="text-muted">সরবরাহকারীদের মোট চালানের পরিমাণ, পরিশোধিত টাকা ও বকেয়া পাওনার তালিকা।</p>
  ' . $this->getCommonIndexGuide('সরবরাহকারী (Supplier)') . '
</div>',
            ],

            // =========================================================================
            // 11. EXPENSE & VOUCHER MODULES
            // =========================================================================
            [
                'model_name' => 'Expense',
                'page_type' => 'create',
                'sorting' => 29,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-money-bill-wave me-2"></i>প্রাত্যহিক খরচ এন্ট্রি (Expense Entry Guide)</h4>
  <p class="text-muted">দোকানের প্রাত্যহিক বিভিন্ন খরচ যেমন দোকান ভাড়া, বিদ্যুৎ বিল, আপ্যায়ন ইত্যাদির হিসাব।</p>
  <ul class="lh-base">
    <li><strong>Expense Head:</strong> খরচের খাত সিলেক্ট করুন।</li>
    <li><strong>Amount:</strong> টাকার পরিমাণ লিখুন।</li>
    <li><strong>Payment Method:</strong> ক্যাশ ড্রয়ার থেকে নাকি ব্যাংক একাউন্ট থেকে খরচ হয়েছে তা উল্লেখ করুন।</li>
  </ul>
</div>',
            ],
            [
                'model_name' => 'Expense',
                'page_type' => 'index',
                'sorting' => 30,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-list-ol me-2"></i>দৈনিক খরচ তালিকা (Expense List Guide)</h4>
  <p class="text-muted">তারিখ ও খাত অনুযায়ী সকল ব্যয়ের ভাউচার তালিকা।</p>
  ' . $this->getCommonIndexGuide('খরচ (Expense)') . '
</div>',
            ],
            [
                'model_name' => 'Voucher',
                'page_type' => 'create',
                'sorting' => 31,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-file-signature me-2"></i>হিসাব ভাউচার তৈরি (Voucher Entry Guide)</h4>
  <p class="text-muted">ডেবিট, ক্রেডিট এবং জার্নাল ভাউচারের মাধ্যমে ডাবল-এন্ট্রি হিসাব লেনদেন সংরক্ষণ।</p>
</div>',
            ],
            [
                'model_name' => 'Voucher',
                'page_type' => 'index',
                'sorting' => 32,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-book me-2"></i>ভাউচার তালিকা (Vouchers List Guide)</h4>
  <p class="text-muted">সকল হিসাব ভাউচারের তালিকা ও স্ট্যাটাস পর্যবেক্ষণ।</p>
  ' . $this->getCommonIndexGuide('ভাউচার (Voucher)') . '
</div>',
            ],

            // =========================================================================
            // 12. HR, EMPLOYEE & PAYROLL MODULES
            // =========================================================================
            [
                'model_name' => 'Employee',
                'page_type' => 'create',
                'sorting' => 33,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-user-tie me-2"></i>কর্মচারী এন্ট্রি সহায়িকা (Employee Setup Guide)</h4>
  <p class="text-muted">কর্মচারীর ব্যক্তিগত তথ্য, পদবী, মূল বেতন ও যোগদানের তারিখ সংরক্ষণ করুন।</p>
</div>',
            ],
            [
                'model_name' => 'Employee',
                'page_type' => 'index',
                'sorting' => 34,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-id-card me-2"></i>কর্মচারী তালিকা সহায়িকা (Employee List Guide)</h4>
  <p class="text-muted">সকল কর্মরত কর্মচারীর প্রোফাইল, পদবী ও বেতন কাঠামো তালিকা।</p>
  ' . $this->getCommonIndexGuide('কর্মচারী (Employee)') . '
</div>',
            ],
            [
                'model_name' => 'SalarySheet',
                'page_type' => 'create',
                'sorting' => 35,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-file-invoice-dollar me-2"></i>মাসিক বেতন বিবরণী তৈরি (Salary Sheet Guide)</h4>
  <p class="text-muted">নির্দিষ্ট মাসের জন্য কর্মচারীদের মূল বেতন, বিভিন্ন ভাতা এবং কর্তন হিসাব করে পে-রোল তৈরি করুন।</p>
</div>',
            ],
            [
                'model_name' => 'SalarySheet',
                'page_type' => 'index',
                'sorting' => 36,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-money-check-alt me-2"></i>বেতন বিবরণী তালিকা (Salary Sheets List)</h4>
  <p class="text-muted">মাসওয়ারী প্রসেসকৃত বেতন শীট ও পরিশোধের হিস্ট্রি।</p>
  ' . $this->getCommonIndexGuide('বেতন শীট (Salary Sheet)') . '
</div>',
            ],
            [
                'model_name' => 'SalaryParameter',
                'page_type' => 'create',
                'sorting' => 37,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-cog me-2"></i>বেতন ভাতা ও কর্তন প্যারামিটার (Salary Parameter Setup)</h4>
  <p class="text-muted">বাড়ি ভাড়া ভাতা, চিকিৎসা ভাতা, যাতায়াত ভাতা কিংবা প্রভিডেন্ট ফান্ড কর্তন কনফিগার করুন।</p>
</div>',
            ],
            [
                'model_name' => 'SalaryParameter',
                'page_type' => 'index',
                'sorting' => 38,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-sliders-h me-2"></i>বেতন প্যারামিটার তালিকা</h4>
  <p class="text-muted">বেতনের ভাতা ও কর্তনের সকল খাতের তালিকা ও হার।</p>
  ' . $this->getCommonIndexGuide('বেতন প্যারামিটার (Salary Parameter)') . '
</div>',
            ],
            [
                'model_name' => 'Designation',
                'page_type' => 'create',
                'sorting' => 39,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-briefcase me-2"></i>পদবী তৈরি সহায়িকা (Designation Setup Guide)</h4>
  <p class="text-muted">দোকান বা প্রতিষ্ঠানের বিভিন্ন পদবী (যেমন: ম্যানেজার, ক্যাশিয়ার, বিক্রয় প্রতিনিধি) তৈরি করুন।</p>
</div>',
            ],
            [
                'model_name' => 'Designation',
                'page_type' => 'index',
                'sorting' => 40,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-user-tag me-2"></i>পদবী তালিকা সহায়িকা (Designations List)</h4>
  <p class="text-muted">সকল পদবীর তালিকা ও দায়িত্বের বিবরণ।</p>
  ' . $this->getCommonIndexGuide('পদবী (Designation)') . '
</div>',
            ],

            // =========================================================================
            // 13. BANKING & ACCOUNTS
            // =========================================================================
            [
                'model_name' => 'Bank',
                'page_type' => 'create',
                'sorting' => 41,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-university me-2"></i>ব্যাংক একাউন্ট তৈরি সহায়িকা (Bank Setup Guide)</h4>
  <p class="text-muted">দোকানের ব্যাংক বা মোবাইল ব্যাংকিং (বিকাশ, নগদ ইত্যাদি) হিসাব যুক্ত করুন।</p>
</div>',
            ],
            [
                'model_name' => 'Bank',
                'page_type' => 'index',
                'sorting' => 42,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-piggy-bank me-2"></i>ব্যাংক একাউন্ট তালিকা ও ব্যালেন্স (Banks List)</h4>
  <p class="text-muted">দোকানের সকল ব্যাংক হিসাবের তালিকা ও বর্তমান ফান্ড ব্যালেন্স।</p>
  ' . $this->getCommonIndexGuide('ব্যাংক একাউন্ট (Bank)') . '
</div>',
            ],
            [
                'model_name' => 'Payment',
                'page_type' => 'create',
                'sorting' => 43,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-hand-holding-usd me-2"></i>সরবরাহকারী বিল পরিশোধ (Supplier Payment Entry)</h4>
  <p class="text-muted">সাপ্লায়ারের বকেয়া বিল ক্যাশ বা ব্যাংকের মাধ্যমে পরিশোধ করার নিয়ম।</p>
</div>',
            ],
            [
                'model_name' => 'Payment',
                'page_type' => 'index',
                'sorting' => 44,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-history me-2"></i>বিল পরিশোধের ইতিহাস (Payment History List)</h4>
  <p class="text-muted">সরবরাহকারীদের পরিশোধিত অর্থের ভাউচার ও তারিখভিত্তিক তালিকা।</p>
  ' . $this->getCommonIndexGuide('বিল পরিশোধ (Payment)') . '
</div>',
            ],
            [
                'model_name' => 'FundTransfer',
                'page_type' => 'create',
                'sorting' => 45,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-exchange-alt me-2"></i>ফান্ড স্থানান্তর সহায়িকা (Fund Transfer Guide)</h4>
  <p class="text-muted">ক্যাশ ড্রয়ার থেকে ব্যাংকে জমা অথবা এক ব্যাংক থেকে অন্য ব্যাংকে টাকা ট্রান্সফারের এন্ট্রি।</p>
</div>',
            ],
            [
                'model_name' => 'FundTransfer',
                'page_type' => 'index',
                'sorting' => 46,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-random me-2"></i>ফান্ড স্থানান্তর তালিকা (Fund Transfers List)</h4>
  <p class="text-muted">ক্যাশ ও ব্যাংকের মধ্যে সম্পন্ন হওয়া সকল অর্থ স্থানান্তরের তালিকা।</p>
  ' . $this->getCommonIndexGuide('ফান্ড স্থানান্তর (Fund Transfer)') . '
</div>',
            ],
            [
                'model_name' => 'Withdrawl',
                'page_type' => 'create',
                'sorting' => 47,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-wallet me-2"></i>মালিকের ব্যক্তিগত উত্তোলন (Owner Drawings / Withdrawal)</h4>
  <p class="text-muted">ব্যবসার মূলধন বা প্রফিট থেকে মালিকের ব্যক্তিগত খরচের জন্য উত্তোলিত টাকার এন্ট্রি।</p>
</div>',
            ],
            [
                'model_name' => 'Withdrawl',
                'page_type' => 'index',
                'sorting' => 48,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-money-bill-alt me-2"></i>উত্তোলন তালিকা (Withdrawals List)</h4>
  <p class="text-muted">ব্যক্তিগত বা পার্টনারশিপ উত্তোলনের হিসাবের খতিয়ান।</p>
  ' . $this->getCommonIndexGuide('উত্তোলন (Withdrawal)') . '
</div>',
            ],
            [
                'model_name' => 'Account',
                'page_type' => 'create',
                'sorting' => 49,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-project-diagram me-2"></i>হিসাবের চার্ট তৈরি (Chart of Accounts Setup)</h4>
  <p class="text-muted">সম্পদ (Asset), দায় (Liability), আয় (Income) ও ব্যয় (Expense) খাত কনফিগার করুন।</p>
</div>',
            ],
            [
                'model_name' => 'Account',
                'page_type' => 'index',
                'sorting' => 50,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-sitemap me-2"></i>হিসাবের চার্ট তালিকা (Chart of Accounts List)</h4>
  <p class="text-muted">ব্যবসায়িক সকল প্রধান খতিয়ান ও সাব-লেজারের তালিকা।</p>
  ' . $this->getCommonIndexGuide('হিসাবের চার্ট (Account)') . '
</div>',
            ],

            // =========================================================================
            // 14. REGIONS, FINANCIAL YEAR & CURRENCY
            // =========================================================================
            [
                'model_name' => 'District',
                'page_type' => 'create',
                'sorting' => 51,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-map-marker-alt me-2"></i>জেলা তৈরি সহায়িকা (District Setup)</h4>
  <p class="text-muted">ডেলিভারি এবং গ্রাহকের ঠিকানা নির্ধারণের জন্য জেলা যুক্ত করুন।</p>
</div>',
            ],
            [
                'model_name' => 'District',
                'page_type' => 'index',
                'sorting' => 52,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-map-marked-alt me-2"></i>জেলা তালিকা সহায়িকা (Districts List)</h4>
  <p class="text-muted">সকল নিবন্ধিত জেলার তালিকা।</p>
  ' . $this->getCommonIndexGuide('জেলা (District)') . '
</div>',
            ],
            [
                'model_name' => 'Area',
                'page_type' => 'create',
                'sorting' => 53,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-location-arrow me-2"></i>এলাকা / থানা তৈরি সহায়িকা (Area Setup)</h4>
  <p class="text-muted">নির্দিষ্ট জেলার আওতায় থানা বা ডেলিভারি এরিয়া তৈরি করুন।</p>
</div>',
            ],
            [
                'model_name' => 'Area',
                'page_type' => 'index',
                'sorting' => 54,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-map me-2"></i>এলাকা তালিকা সহায়িকা (Areas List)</h4>
  <p class="text-muted">জেলার আওতাধীন সকল এলাকা ও থানার তালিকা।</p>
  ' . $this->getCommonIndexGuide('এলাকা (Area)') . '
</div>',
            ],
            [
                'model_name' => 'FinancialYear',
                'page_type' => 'create',
                'sorting' => 55,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-calendar-check me-2"></i>আর্থিক বছর নির্ধারণ (Financial Year Setup)</h4>
  <p class="text-muted">হিসাব-নিকাশের জন্য আর্থিক বছরের শুরুর ও শেষের তারিখ নির্ধারণ করুন।</p>
</div>',
            ],
            [
                'model_name' => 'FinancialYear',
                'page_type' => 'index',
                'sorting' => 56,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-calendar-alt me-2"></i>আর্থিক বছর তালিকা (Financial Years List)</h4>
  <p class="text-muted">সকল আর্থিক বছরের তালিকা এবং বর্তমান সক্রিয় আর্থিক বছর।</p>
  ' . $this->getCommonIndexGuide('আর্থিক বছর (Financial Year)') . '
</div>',
            ],
            [
                'model_name' => 'Currency',
                'page_type' => 'create',
                'sorting' => 57,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-coins me-2"></i>মুদ্রা নির্ধারণ সহায়িকা (Currency Setup)</h4>
  <p class="text-muted">কারেন্সি কোড (যেমন: BDT, USD) এবং এক্সচেঞ্জ রেট কনফিগার করুন।</p>
</div>',
            ],
            [
                'model_name' => 'Currency',
                'page_type' => 'index',
                'sorting' => 58,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-money-check me-2"></i>মুদ্রা তালিকা (Currencies List)</h4>
  <p class="text-muted">সিস্টেমের সক্রিয় মুদ্রার তালিকা।</p>
  ' . $this->getCommonIndexGuide('মুদ্রা (Currency)') . '
</div>',
            ],

            // =========================================================================
            // 15. SYSTEM & SITE SETTINGS
            // =========================================================================
            [
                'model_name' => 'SiteSetting',
                'page_type' => 'create',
                'sorting' => 59,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-sliders-h me-2"></i>সিস্টেম ও শপ সেটিংস সহায়িকা (Site Settings Guide)</h4>
  <p class="text-muted">দোকানের মৌলিক কনফিগারেশন, প্রিন্টার এবং শপ টাইপ নির্ধারণের পেজ।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-store text-success me-2"></i>গুরুত্বপূর্ণ কনফিগারেশনসমূহ:</h5>
  <ul class="lh-base">
    <li><strong>Shop Type:</strong>
      <ul>
        <li><code>Electronics</code>: নির্বাচন করলে সাইজ (Size) অপশন লুকিয়ে যাবে এবং সিরিয়াল নম্বর ও ওয়ারেন্টির অপশনগুলো প্রধানভাবে সক্রিয় হবে।</li>
        <li><code>Clothing</code>: নির্বাচন করলে পোশাকের কালার এবং সাইজ (S, M, L, XL ইত্যাদি) ম্যাট্রিক্স উন্মুক্ত থাকবে।</li>
      </ul>
    </li>
    <li><strong>Printer Type & Paper Size:</strong>
      <ul>
        <li><code>Thermal</code>: থার্মাল রিসিপ্ট প্রিন্টারের জন্য (80mm বা 58mm পেপার সাইজ নির্বাচন করুন)।</li>
        <li><code>Normal</code>: সাধারণ ডেস্কটপ প্রিন্টারের জন্য A4 বা A5 সাইজের ইনভয়েস কনফিগারেশন।</li>
      </ul>
    </li>
    <li><strong>Barcode Label Preset:</strong> বারকোড স্টিকার প্রিন্ট করার মাপ (যেমন: 4x2 ইত্যাদি) নির্বাচন করুন।</li>
  </ul>
</div>',
            ],
            [
                'model_name' => 'SiteSetting',
                'page_type' => 'view',
                'sorting' => 60,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-info-circle me-2"></i>দোকান ও সিস্টেম কনফিগারেশন সারসংক্ষেপ</h4>
  <p class="text-muted">বর্তমান সক্রিয় শপ টাইপ, প্রিন্টার সাইজ ও কোম্পানির তথ্য প্রিভিউ।</p>
</div>',
            ],
            [
                'model_name' => 'Role',
                'page_type' => 'create',
                'sorting' => 61,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-user-shield me-2"></i>ব্যবহারকারী রোল তৈরি (User Role & Permissions)</h4>
  <p class="text-muted">ম্যানেজার, ক্যাশিয়ার বা সেলসম্যানের জন্য আলাদা আলাদা পারমিশন বা অনুমতি সেট করুন।</p>
</div>',
            ],
            [
                'model_name' => 'Role',
                'page_type' => 'index',
                'sorting' => 62,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-shield-alt me-2"></i>ব্যবহারকারী রোল তালিকা (Roles List)</h4>
  <p class="text-muted">সকল রোল ও সংশ্লিষ্ট মডিউলের পারমিশন তালিকা।</p>
  ' . $this->getCommonIndexGuide('ব্যবহারকারী রোল (Role)') . '
</div>',
            ],
            [
                'model_name' => 'Menu',
                'page_type' => 'create',
                'sorting' => 63,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-bars me-2"></i>মেনু আইটেম তৈরি (Backend Menu Setup)</h4>
  <p class="text-muted">সাইডবার নেভিগেশনে নতুন মেনু বা সাব-মেনু যুক্ত করার পেজ।</p>
</div>',
            ],
            [
                'model_name' => 'Menu',
                'page_type' => 'index',
                'sorting' => 64,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-th-list me-2"></i>সাইডবার মেনু তালিকা (Menus List)</h4>
  <p class="text-muted">সিস্টেমের সকল সাইডবার মেনুর ক্রমানুসার ও কনফিগারেশন।</p>
  ' . $this->getCommonIndexGuide('মেনু (Menu)') . '
</div>',
            ],
            [
                'model_name' => 'Admin',
                'page_type' => 'Create',
                'sorting' => 65,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-user-cog me-2"></i>সিস্টেম ইউজার তৈরি (System User Setup)</h4>
  <p class="text-muted">সফ্টওয়্যার ব্যবহারের জন্য নতুন ইউজার একাউন্ট, ইমেইল, পাসওয়ার্ড ও রোল অ্যাসাইন করুন।</p>
</div>',
            ],
            [
                'model_name' => 'Admin',
                'page_type' => 'Index',
                'sorting' => 66,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-users-cog me-2"></i>সিস্টেম ইউজার তালিকা (Admin Users List)</h4>
  <p class="text-muted">সকল ব্যবহারকারীর প্রোফাইল ও একাউন্ট স্ট্যাটাস।</p>
  ' . $this->getCommonIndexGuide('সিস্টেম ইউজার (Admin)') . '
</div>',
            ],

            // =========================================================================
            // 16. REPORTS (REPORTS SECTION)
            // =========================================================================
            [
                'model_name' => 'SalesReport',
                'page_type' => 'index',
                'sorting' => 70,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-chart-bar me-2"></i>বিক্রয় প্রতিবেদন সহায়িকা (Sales Report Guide)</h4>
  <p class="text-muted">দোকানের মোট বিক্রয়, ছাড়, নগদ আদায় ও বকেয়ার সামগ্রিক রিপোর্ট।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-layer-group text-success me-2"></i>৪ ধরনের বিশ্লেষণ ভিউ (Breakdown Tabs):</h5>
  <ul class="lh-base">
    <li><strong>১. ইনভয়েস ব্রেকডাউন (Invoices Breakdown):</strong> প্রতিটি বিক্রয় চালানের বিস্তারিত নম্বর, কাস্টমার, আইটেম সংখ্যা, মোট মূল্য, ক্যাশ আদায় ও বকেয়া দেখুন।</li>
    <li><strong>২. পণ্যভিত্তিক বিক্রয় (Item-Wise Sales):</strong> কোন পণ্যটি কত পিস বিক্রয় হয়েছে, গড় বিক্রয়মূল্য এবং মোট কত টাকার বিক্রি হয়েছে তার তালিকা।</li>
    <li><strong>৩. কাস্টমারভিত্তিক বিশ্লেষণ (Customer Breakdown):</strong> শীর্ষ গ্রাহকদের ক্রয়কৃত ইনভয়েস সংখ্যা, মোট কেনাকাটা এবং বকেয়ার তথ্য।</li>
    <li><strong>৪. দৈনিক বিক্রয় বিবরণী (Daily Breakdown):</strong> মাসের প্রতিদিনের বিক্রয়, মোট ভ্যাট, ডিসকাউন্ট ও আদায়ের সামারি।</li>
  </ul>
  ' . $this->getCommonReportGuide('বিক্রয় প্রতিবেদন (Sales Report)') . '
</div>',
            ],
            [
                'model_name' => 'AvailableStock',
                'page_type' => 'index',
                'sorting' => 71,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-warehouse me-2"></i>মজুদ পণ্য ও স্টক ভ্যালুয়েশন সহায়িকা (Available Stock Guide)</h4>
  <p class="text-muted">দোকানের বর্তমান সক্রিয় মজুদ (Stock Qty) এবং মোট টাকার মূলধন (Stock Value) নিরীক্ষণ করুন।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-boxes text-info me-2"></i>প্রধান সুবিধাসমূহ:</h5>
  <ul class="lh-base">
    <li><strong>ক্যাটাগরি ও ব্র্যান্ড ফিল্টার:</strong> নির্দিষ্ট কোনো ক্যাটাগরি বা ব্র্যান্ডের কী পরিমাণ পণ্য স্টকে আছে তা আলাদা করুন।</li>
    <li><strong>ভ্যারিয়েন্ট অনুযায়ী স্টক:</strong> কালার এবং সাইজ ভিত্তিক নির্দিষ্ট স্টক পরিমাণ দেখুন।</li>
    <li><strong>স্টক মূলধন (Total Value):</strong> ক্রয়মূল্য ও বিক্রয়মূল্যের ভিত্তিতে দোকানে বর্তমানে মোট কত টাকার পণ্য মজুদ রয়েছে তার স্বয়ংক্রিয় হিসাব।</li>
    <li><strong>লো স্টক অ্যালার্ট:</strong> ফুরিয়ে যাওয়া বা সীমিত স্টক বিশিষ্ট পণ্যগুলো দ্রুত শনাক্ত করুন।</li>
  </ul>
  ' . $this->getCommonReportGuide('মজুদ পণ্য প্রতিবেদন (Available Stock Report)') . '
</div>',
            ],
            [
                'model_name' => 'DailyReport',
                'page_type' => 'index',
                'sorting' => 72,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-book-open me-2"></i>আইটেম লেজার প্রতিবেদন সহায়িকা (Item Ledger Report)</h4>
  <p class="text-muted">নির্দিষ্ট পণ্যের তারিখভিত্তিক স্টক ইন (ক্রয়), স্টক আউট (বিক্রয়/অপচয়) এবং অবশিষ্ট ব্যালেন্সের ইতিহাস।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-history text-primary me-2"></i>কীভাবে ব্যবহার করবেন:</h5>
  <ol class="lh-base">
    <li>পণ্য সার্চ বক্সে কাঙ্ক্ষিত পণ্যের নাম বা বারকোড নির্বাচন করুন।</li>
    <li>তারিখের পরিসীমা (From Date - To Date) দিন এবং Search চাপুন।</li>
    <li>উক্ত সময়ের মধ্যে পণ্যটি কত পিস ঢুকেছে, কোন ইনভয়েসে কত পিস বিক্রয় হয়েছে এবং দিনশেষে কত পিস ব্যালেন্স ছিল তার স্পষ্ট টাইমলাইন দেখতে পাবেন।</li>
  </ol>
  ' . $this->getCommonReportGuide('আইটেম লেজার (Item Ledger)') . '
</div>',
            ],
            [
                'model_name' => 'IncomeStatement',
                'page_type' => 'index',
                'sorting' => 73,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-file-invoice-dollar me-2"></i>আয় বিবরণী ও লাভ-ক্ষতি প্রতিবেদন (Income Statement / P&L)</h4>
  <p class="text-muted">দোকানের মোট বিক্রয় রাজস্ব, বিক্রীত পণ্যের ক্রয় খরচ (COGS), মোট প্রফিট, পরিচালন ব্যয় ও নিট মুনাফা।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-calculator text-success me-2"></i>হিসাব পদ্ধতি:</h5>
  <ul class="lh-base">
    <li><strong>মোট বিক্রয় আয় (Gross Revenue):</strong> নির্বাচিত সময়ের মোট বিক্রয় ইনভয়েস থেকে বাদ দেওয়া ডিসকাউন্টের নিট হিসাব।</li>
    <li><strong>বিক্রীত পণ্যের খরচ (Cost of Goods Sold):</strong> যে পণ্যগুলো বিক্রি হয়েছে সেগুলোর মূল ক্রয়মূল্য।</li>
    <li><strong>মোট মুনাফা (Gross Profit):</strong> মোট বিক্রয় আয় থেকে ক্রয় খরচ বাদ দিয়ে প্রাপ্ত মুনাফা।</li>
    <li><strong>পরিচালন ব্যয় (Operating Expenses):</strong> দোকান ভাড়া, বিদ্যুৎ, বেতন ও প্রাত্যহিক খরচের যোগফল।</li>
    <li><strong>নিট লাভ / ক্ষতি (Net Profit):</strong> মোট মুনাফা থেকে মোট পরিচালন ব্যয় বাদ দিলে প্রকৃত নিট লাভ বা ক্ষতি পাওয়া যায়।</li>
  </ul>
  ' . $this->getCommonReportGuide('আয় বিবরণী (Income Statement)') . '
</div>',
            ],
            [
                'model_name' => 'ExpenseStatement',
                'page_type' => 'index',
                'sorting' => 74,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-hand-holding-usd me-2"></i>ব্যয় ও খরচ বিবরণী সহায়িকা (Expense Statement Guide)</h4>
  <p class="text-muted">খাতভিত্তিক ব্যয়ের বিস্তারিত এবং নির্দিষ্ট সময়ের মধ্যে মোট কত টাকা খরচ হয়েছে তার হিসাব।</p>
  ' . $this->getCommonReportGuide('ব্যয় বিবরণী (Expense Statement)') . '
</div>',
            ],
            [
                'model_name' => 'Trialbalance',
                'page_type' => 'index',
                'sorting' => 75,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-balance-scale me-2"></i>রেওয়ামিল ও খতিয়ান প্রতিবেদন (Trial Balance / Ledger)</h4>
  <p class="text-muted">হিসাবের গাণিতিক নির্ভুলতা যাচাইয়ের জন্য সকল খতিয়ানের ডেবিট ও ক্রেডিট ব্যালেন্সের সমতা এবং গ্রাহক/সাপ্লায়ার খতিয়ান স্টেটমেন্ট।</p>
  ' . $this->getCommonReportGuide('রেওয়ামিল ও খতিয়ান (Trial Balance)') . '
</div>',
            ],
            [
                'model_name' => 'Agentledger',
                'page_type' => 'index',
                'sorting' => 76,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-user-friends me-2"></i>এজেন্ট খতিয়ান প্রতিবেদন (Agent Ledger Report)</h4>
  <p class="text-muted">বিক্রয় এজেন্ট বা রেফারাল প্রতিনিধির কমিশন অর্জন, উত্তোলন ও বকেয়া পাওনার হিসাব।</p>
  ' . $this->getCommonReportGuide('এজেন্ট খতিয়ান (Agent Ledger)') . '
</div>',
            ],
            [
                'model_name' => 'Funds',
                'page_type' => 'index',
                'sorting' => 77,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-money-bill-wave me-2"></i>ফান্ড ব্যালেন্স ও ক্যাশ ফ্লো রিপোর্ট (Funds Summary)</h4>
  <p class="text-muted">ক্যাশ ইন হ্যান্ড (ক্যাশ ড্রয়ার) এবং বিভিন্ন ব্যাংকের বর্তমান স্থিতি ও তারল্য রিপোর্ট।</p>
  ' . $this->getCommonReportGuide('ফান্ড ব্যালেন্স (Funds Report)') . '
</div>',
            ],
            [
                'model_name' => 'Report',
                'page_type' => 'index',
                'sorting' => 78,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-ticket-alt me-2"></i>কুপন ও লয়্যালটি পয়েন্ট রিপোর্ট (Coupon & Loyalty Report)</h4>
  <p class="text-muted">গ্রাহকের লয়্যালটি পয়েন্ট অর্জন, রিডিম করা পয়েন্ট এবং ডিসকাউন্ট কুপন ব্যবহারের রিপোর্ট।</p>
  ' . $this->getCommonReportGuide('কুপন ও পয়েন্ট (Coupon Report)') . '
</div>',
            ],
            [
                'model_name' => 'SerialReport',
                'page_type' => 'index',
                'sorting' => 79,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-barcode me-2"></i>সিরিয়াল ও ওয়ারেন্টি লাইফসাইকেল অডিট সহায়িকা (Serial Audit Guide)</h4>
  <p class="text-muted">ইলেকট্রনিক্স পণ্যের প্রতিটি সিরিয়াল নম্বরের বর্তমান অবস্থান, বিক্রয় স্থিতি ও ওয়ারেন্টির মেয়াদ।</p>

  <h5 class="text-dark mt-3"><i class="fas fa-search-plus text-primary me-2"></i>প্রধান ফিচারসমূহ:</h5>
  <ul class="lh-base">
    <li><strong>সিরিয়াল নম্বর দিয়ে দ্রুত সার্চ:</strong> যে কোনো সিরিয়াল নম্বর টাইপ করলেই পণ্যটি কোন চালানে ইন হয়েছিল এবং কোন ইনভয়েসে কার কাছে বিক্রি হয়েছিল তা তৎক্ষণাৎ বের হয়ে আসবে।</li>
    <li><strong>ওয়ারেন্টির মেয়াদ পর্যবেক্ষণ:</strong> সিরিয়ালটির ওয়ারেন্টির মেয়াদ আর কতদিন বাকি রয়েছে (Active) নাকি শেষ হয়ে গেছে (Expired) তা কালার ব্যাজে দেখা যাবে।</li>
    <li><strong>ওয়ান-ক্লিক ক্লেইম:</strong> যে কোনো বিক্রিত সিরিয়ালের পাশে থাকা <strong>Claim</strong> বাটনে ক্লিক করে সরাসরি ওয়ারেন্টি সার্ভিসের এন্ট্রি করতে পারেন।</li>
  </ul>
  ' . $this->getCommonReportGuide('সিরিয়াল অডিট রিপোর্ট (Serial Report)') . '
</div>',
            ],
            [
                'model_name' => 'WarrantyClaimReport',
                'page_type' => 'index',
                'sorting' => 80,
                'status' => 'active',
                'description' => '
<div class="help-manual-box p-2">
  <h4 class="text-primary mb-2"><i class="fas fa-tools me-2"></i>ওয়ারেন্টি ক্লেইম অডিট প্রতিবেদন সহায়িকা (Warranty Claims Audit)</h4>
  <p class="text-muted">দোকানে জমা পড়া সকল ওয়ারেন্টি ও গ্যারান্টি সার্ভিসের অডিট, ভেন্ডর ডেলিভারি, সার্ভিস খরচ ও স্ট্যাটাস রিপোর্ট।</p>
  ' . $this->getCommonReportGuide('ওয়ারেন্টি অডিট (Warranty Claim Audit)') . '
</div>',
            ],
        ];

        foreach ($manuals as $manual) {
            DB::table('help_infos')->updateOrInsert(
                [
                    'model_name' => $manual['model_name'],
                    'page_type' => $manual['page_type'],
                ],
                [
                    'sorting' => $manual['sorting'],
                    'status' => $manual['status'],
                    'description' => trim($manual['description']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Successfully seeded ' . count($manuals) . ' Bengali help manuals with index & report common guides into help_infos table.');
    }
}
