<?php

namespace Database\Seeders;

use App\Models\HelpEntry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HelpEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds the help_entries table with Arabic Q&A content
     * based on actual system features.
     */
    public function run(): void
    {
        $entries = [
            // ============================================
            // الطلبات (Orders)
            // ============================================
            [
                'category' => 'orders',
                'question' => 'كيف يمكنني إلغاء طلب؟',
                'answer' => '<p>لإلغاء طلب، اتبع الخطوات التالية:</p>
                    <ol>
                        <li>اذهب إلى قسم <strong>المبيعات</strong> ثم <strong>الطلبات</strong></li>
                        <li>ابحث عن الطلب المراد إلغاؤه</li>
                        <li>اضغط على زر <strong>عرض</strong> لفتح تفاصيل الطلب</li>
                        <li>اضغط على زر <strong>إلغاء الطلب</strong></li>
                        <li>أدخل <strong>سبب الإلغاء</strong> في الحقل المخصص (إلزامي)</li>
                        <li>اضغط <strong>تأكيد</strong></li>
                    </ol>
                    <p><strong>ملاحظة:</strong> لا يمكن إلغاء الطلبات التي تم تسليمها بالفعل.</p>',
                'sort_order' => 1,
            ],
            [
                'category' => 'orders',
                'question' => 'ما هي حالات الطلب المختلفة؟',
                'answer' => '<p>يمر الطلب بعدة حالات:</p>
                    <ul>
                        <li><strong>معلق (Pending):</strong> الطلب جديد وينتظر المراجعة</li>
                        <li><strong>قيد التجهيز (Processing):</strong> يتم تجهيز الطلب للشحن</li>
                        <li><strong>تم الشحن (Shipped):</strong> الطلب في الطريق للعميل</li>
                        <li><strong>تم التسليم (Delivered):</strong> وصل الطلب للعميل بنجاح</li>
                        <li><strong>ملغي (Cancelled):</strong> تم إلغاء الطلب</li>
                    </ul>',
                'sort_order' => 2,
            ],
            [
                'category' => 'orders',
                'question' => 'كيف أعرف سبب رفض أو إلغاء طلب؟',
                'answer' => '<p>عند إلغاء أو رفض طلب، يتم تسجيل السبب في حقلين:</p>
                    <ul>
                        <li><strong>سبب الإلغاء (Cancellation Reason):</strong> يظهر في تفاصيل الطلب تحت قسم "معلومات الإلغاء"</li>
                        <li><strong>سبب الرفض (Rejection Reason):</strong> يستخدم عند رفض طلب من قبل الإدارة</li>
                    </ul>
                    <p>يمكنك رؤية هذه المعلومات في صفحة تفاصيل الطلب، وأيضاً في سجل حالات الطلب.</p>',
                'sort_order' => 3,
            ],
            [
                'category' => 'orders',
                'question' => 'كيف أتتبع حالة الدفع للطلب؟',
                'answer' => '<p>يمكنك معرفة حالة الدفع من خلال:</p>
                    <ol>
                        <li>فتح صفحة تفاصيل الطلب</li>
                        <li>البحث عن حقل <strong>حالة الدفع</strong></li>
                    </ol>
                    <p>الحالات المتاحة:</p>
                    <ul>
                        <li><strong>غير مدفوع:</strong> لم يتم الدفع بعد</li>
                        <li><strong>معلق:</strong> في انتظار تأكيد الدفع</li>
                        <li><strong>مدفوع:</strong> تم الدفع بنجاح</li>
                    </ul>',
                'sort_order' => 4,
            ],

            // ============================================
            // التسويق (Marketing)
            // ============================================
            [
                'category' => 'marketing',
                'question' => 'كيف أقوم بإعداد Facebook Pixel؟',
                'answer' => '<p>لإعداد Facebook Pixel لتتبع الإعلانات:</p>
                    <ol>
                        <li>اذهب إلى <strong>النظام</strong> ثم <strong>الإعدادات</strong></li>
                        <li>ابحث عن إعداد <strong>facebook_pixel_id</strong></li>
                        <li>أدخل رقم الـ Pixel ID الخاص بك (يمكنك الحصول عليه من Facebook Business Manager)</li>
                        <li>اضغط <strong>حفظ</strong></li>
                    </ol>
                    <p><strong>ملاحظة:</strong> بعد الإعداد، سيتم تتبع أحداث مثل مشاهدة المنتجات وإضافة للسلة والشراء تلقائياً.</p>',
                'sort_order' => 1,
            ],
            [
                'category' => 'marketing',
                'question' => 'كيف أنشئ كود خصم جديد؟',
                'answer' => '<p>لإنشاء كود خصم:</p>
                    <ol>
                        <li>اذهب إلى <strong>المبيعات</strong> ثم <strong>أكواد الخصم</strong></li>
                        <li>اضغط <strong>إنشاء كود خصم</strong></li>
                        <li>أدخل البيانات المطلوبة:
                            <ul>
                                <li><strong>الكود:</strong> يمكنك إدخاله يدوياً أو توليده تلقائياً</li>
                                <li><strong>نوع الخصم:</strong> نسبة مئوية أو مبلغ ثابت أو شحن مجاني</li>
                                <li><strong>القيمة:</strong> قيمة الخصم</li>
                                <li><strong>تاريخ الانتهاء:</strong> متى ينتهي الكود</li>
                            </ul>
                        </li>
                        <li>اضغط <strong>إنشاء</strong></li>
                    </ol>',
                'sort_order' => 2,
            ],
            [
                'category' => 'marketing',
                'question' => 'كيف أربط كود خصم بمؤثر؟',
                'answer' => '<p>لربط كود خصم بمؤثر معين:</p>
                    <ol>
                        <li>عند إنشاء أو تعديل كود الخصم</li>
                        <li>اختر <strong>نوع الكوبون</strong>: مؤثر</li>
                        <li>من حقل <strong>المؤثر</strong>، اختر المؤثر المطلوب</li>
                        <li>حدد <strong>نسبة العمولة</strong> للمؤثر</li>
                    </ol>
                    <p>سيتم احتساب العمولة تلقائياً عند استخدام الكود في أي طلب.</p>',
                'sort_order' => 3,
            ],

            // ============================================
            // المخزون (Inventory)
            // ============================================
            [
                'category' => 'inventory',
                'question' => 'ماذا تعني علامة "غير متوفر" على المنتج؟',
                'answer' => '<p>تظهر علامة <strong>"غير متوفر"</strong> (شارة نصية حمراء) على المنتج في الحالات التالية:</p>
                    <ul>
                        <li>عندما يكون رصيد المخزون <strong>صفر أو أقل</strong></li>
                        <li>تظهر هذه العلامة للعملاء على صفحة المنتج وفي قوائم المنتجات</li>
                    </ul>
                    <p><strong>ملاحظة:</strong> المنتجات غير المتوفرة لا يمكن إضافتها للسلة.</p>',
                'sort_order' => 1,
            ],
            [
                'category' => 'inventory',
                'question' => 'كيف أضيف مخزون لمنتج؟',
                'answer' => '<p>لإضافة مخزون لمنتج موجود:</p>
                    <ol>
                        <li>اذهب إلى <strong>المخزون</strong> ثم <strong>حركات المخزون</strong></li>
                        <li>اضغط <strong>إضافة حركة</strong></li>
                        <li>اختر <strong>نوع الحركة</strong>: إضافة (In)</li>
                        <li>اختر <strong>المنتج</strong></li>
                        <li>أدخل <strong>الكمية</strong></li>
                        <li>اختياري: أدخل رقم الدفعة وملاحظات</li>
                        <li>اضغط <strong>حفظ</strong></li>
                    </ol>',
                'sort_order' => 2,
            ],
            [
                'category' => 'inventory',
                'question' => 'ما هو "جرد المخزون" وكيف أستخدمه؟',
                'answer' => '<p><strong>جرد المخزون</strong> يسمح لك بمقارنة الكميات الفعلية مع النظام:</p>
                    <ol>
                        <li>اذهب إلى <strong>المخزون</strong> ثم <strong>جرد المخزون</strong></li>
                        <li>اضغط <strong>بدء جرد جديد</strong></li>
                        <li>اختر المخزن والمنتجات المراد جردها</li>
                        <li>أدخل الكميات الفعلية لكل منتج</li>
                        <li>النظام سيحسب الفروقات تلقائياً</li>
                        <li>راجع الفروقات واضغط <strong>تطبيق الجرد</strong></li>
                    </ol>
                    <p>سيتم تعديل المخزون تلقائياً بناءً على نتائج الجرد.</p>',
                'sort_order' => 3,
            ],
            [
                'category' => 'inventory',
                'question' => 'كيف أعرف المنتجات منخفضة المخزون؟',
                'answer' => '<p>يمكنك متابعة المنتجات منخفضة المخزون من خلال:</p>
                    <ol>
                        <li><strong>لوحة التحكم:</strong> ويدجت "تنبيه المخزون المنخفض" يظهر عدد المنتجات</li>
                        <li><strong>قائمة مخصصة:</strong> اذهب إلى <strong>المخزون</strong> ثم <strong>منتجات مخزون منخفض</strong></li>
                    </ol>
                    <p>يعتبر المنتج منخفض المخزون عندما يكون رصيده أقل من الحد الأدنى المحدد (افتراضياً: 5 وحدات).</p>',
                'sort_order' => 4,
            ],

            // ============================================
            // المبيعات (Sales)
            // ============================================
            [
                'category' => 'sales',
                'question' => 'كيف أعرض تقرير المبيعات؟',
                'answer' => '<p>لعرض تقرير المبيعات:</p>
                    <ol>
                        <li>اذهب إلى <strong>المبيعات</strong> ثم <strong>تقرير المبيعات</strong></li>
                        <li>استخدم الفلاتر الموجودة:
                            <ul>
                                <li><strong>تاريخ البدء:</strong> لتحديد بداية الفترة</li>
                                <li><strong>تاريخ الانتهاء:</strong> لتحديد نهاية الفترة</li>
                                <li><strong>طريقة الدفع:</strong> لتصفية حسب نوع الدفع</li>
                            </ul>
                        </li>
                        <li>يمكنك تصدير التقرير إلى Excel بالضغط على زر <strong>تصدير Excel</strong></li>
                    </ol>',
                'sort_order' => 1,
            ],
            [
                'category' => 'sales',
                'question' => 'ما هي طرق الدفع المدعومة؟',
                'answer' => '<p>يدعم النظام طرق الدفع التالية:</p>
                    <ul>
                        <li><strong>الدفع عند الاستلام (COD):</strong> يدفع العميل للمندوب عند التسليم</li>
                        <li><strong>بطاقة ائتمان:</strong> عبر بوابة Paymob</li>
                        <li><strong>المحفظة الإلكترونية:</strong> Vodafone Cash, Orange Money, إلخ</li>
                        <li><strong>InstaPay:</strong> تحويل بنكي فوري</li>
                    </ul>
                    <p>يمكن تفعيل/تعطيل طرق الدفع من <strong>إعدادات الدفع</strong>.</p>',
                'sort_order' => 2,
            ],
            [
                'category' => 'sales',
                'question' => 'كيف أعدّل إعدادات بوابة الدفع؟',
                'answer' => '<p>لتعديل إعدادات بوابة الدفع:</p>
                    <ol>
                        <li>اذهب إلى <strong>المبيعات</strong> ثم <strong>إعدادات الدفع</strong></li>
                        <li>اختر <strong>البوابة النشطة</strong> (Paymob أو Kashier)</li>
                        <li>أدخل بيانات الاعتماد المطلوبة:
                            <ul>
                                <li>API Key</li>
                                <li>Integration IDs (للبطاقة والمحفظة والكيوسك)</li>
                                <li>HMAC Secret</li>
                            </ul>
                        </li>
                        <li>فعّل طرق الدفع المطلوبة</li>
                        <li>اضغط <strong>حفظ الإعدادات</strong></li>
                    </ol>',
                'sort_order' => 3,
            ],

            // ============================================
            // النظام (System)
            // ============================================
            [
                'category' => 'system',
                'question' => 'كيف أغير كلمة المرور الخاصة بي؟',
                'answer' => '<p>لتغيير كلمة المرور:</p>
                    <ol>
                        <li>اضغط على اسمك في أعلى الصفحة</li>
                        <li>اختر <strong>الملف الشخصي</strong></li>
                        <li>انتقل إلى قسم <strong>كلمة المرور</strong></li>
                        <li>أدخل كلمة المرور الحالية</li>
                        <li>أدخل كلمة المرور الجديدة (يجب أن تكون 8 أحرف على الأقل)</li>
                        <li>أكد كلمة المرور الجديدة</li>
                        <li>اضغط <strong>حفظ</strong></li>
                    </ol>',
                'sort_order' => 1,
            ],
            [
                'category' => 'system',
                'question' => 'كيف أضيف مستخدم جديد للنظام؟',
                'answer' => '<p>لإضافة مستخدم جديد:</p>
                    <ol>
                        <li>اذهب إلى <strong>النظام</strong> ثم <strong>المستخدمين</strong></li>
                        <li>اضغط <strong>إنشاء مستخدم</strong></li>
                        <li>أدخل البيانات المطلوبة:
                            <ul>
                                <li>الاسم</li>
                                <li>البريد الإلكتروني</li>
                                <li>الهاتف (اختياري)</li>
                                <li>كلمة المرور</li>
                                <li>الدور (مدير، موظف مبيعات، إلخ)</li>
                            </ul>
                        </li>
                        <li>اضغط <strong>إنشاء</strong></li>
                    </ol>
                    <p>سيتمكن المستخدم من تسجيل الدخول مباشرة.</p>',
                'sort_order' => 2,
            ],
            [
                'category' => 'system',
                'question' => 'ما هي الأدوار والصلاحيات؟',
                'answer' => '<p>نظام الأدوار والصلاحيات يتحكم في ما يمكن لكل مستخدم رؤيته وفعله:</p>
                    <ul>
                        <li><strong>الأدوار:</strong> مجموعات محددة مسبقاً من الصلاحيات (مثل: مدير، موظف مبيعات، أمين مخزن)</li>
                        <li><strong>الصلاحيات:</strong> إجراءات محددة (عرض، إنشاء، تعديل، حذف)</li>
                    </ul>
                    <p>لتعديل صلاحيات دور معين:</p>
                    <ol>
                        <li>اذهب إلى <strong>النظام</strong> ثم <strong>صلاحيات الأدوار</strong></li>
                        <li>اختر الدور المراد تعديله</li>
                        <li>فعّل أو عطّل الصلاحيات المطلوبة</li>
                    </ol>',
                'sort_order' => 3,
            ],
            [
                'category' => 'system',
                'question' => 'كيف أغير شعار المتجر؟',
                'answer' => '<p>لتغيير شعار المتجر:</p>
                    <ol>
                        <li>اذهب إلى <strong>النظام</strong> ثم <strong>الإعدادات</strong></li>
                        <li>ابحث عن إعداد <strong>site_logo</strong></li>
                        <li>اضغط على حقل الصورة</li>
                        <li>ارفع الشعار الجديد (يفضل: PNG شفاف، أبعاد 200×50 بكسل)</li>
                        <li>اضغط <strong>حفظ</strong></li>
                    </ol>
                    <p>سيظهر الشعار الجديد فوراً في واجهة المتجر ولوحة الإدارة.</p>',
                'sort_order' => 4,
            ],
            [
                'category' => 'system',
                'question' => 'كيف أتواصل مع الدعم الفني؟',
                'answer' => '<p>للتواصل مع الدعم الفني:</p>
                    <ul>
                        <li><strong>البريد الإلكتروني:</strong> support@example.com</li>
                        <li><strong>واتساب:</strong> 01xxxxxxxxx</li>
                        <li><strong>ساعات العمل:</strong> من 9 صباحاً إلى 5 مساءً (الأحد - الخميس)</li>
                    </ul>
                    <p>يرجى توفير المعلومات التالية عند الإبلاغ عن مشكلة:</p>
                    <ul>
                        <li>وصف المشكلة</li>
                        <li>الخطوات التي أدت للمشكلة</li>
                        <li>لقطة شاشة (إن أمكن)</li>
                    </ul>',
                'sort_order' => 5,
            ],

            // ============================================
            // المنتجات (Products)
            // ============================================
            [
                'category' => 'products',
                'question' => 'كيف أضيف منتج جديد؟',
                'answer' => '<p>لإضافة منتج جديد:</p>
                    <ol>
                        <li>اذهب إلى <strong>الكتالوج</strong> ثم <strong>المنتجات</strong></li>
                        <li>اضغط <strong>إنشاء منتج</strong></li>
                        <li>أدخل البيانات الأساسية:
                            <ul>
                                <li>اسم المنتج</li>
                                <li>الوصف</li>
                                <li>السعر وسعر المقارنة</li>
                                <li>الفئة</li>
                                <li>كود المنتج (SKU)</li>
                            </ul>
                        </li>
                        <li>ارفع صور المنتج</li>
                        <li>حدد كمية المخزون الأولية</li>
                        <li>اضغط <strong>إنشاء</strong></li>
                    </ol>',
                'sort_order' => 1,
            ],
            [
                'category' => 'products',
                'question' => 'كيف أعدّل صور المنتج؟',
                'answer' => '<p>لتعديل صور المنتج:</p>
                    <ol>
                        <li>افتح صفحة تعديل المنتج</li>
                        <li>انتقل إلى قسم <strong>الصور</strong></li>
                        <li>لإضافة صورة: اضغط على منطقة الرفع واختر الصور</li>
                        <li>لحذف صورة: اضغط على أيقونة X فوق الصورة</li>
                        <li>لتغيير الترتيب: اسحب الصور وأفلتها</li>
                        <li>اضغط <strong>حفظ</strong></li>
                    </ol>
                    <p><strong>نصائح:</strong></p>
                    <ul>
                        <li>استخدم صور عالية الجودة (800×800 بكسل على الأقل)</li>
                        <li>الصورة الأولى تظهر كصورة رئيسية</li>
                    </ul>',
                'sort_order' => 2,
            ],
        ];

        foreach ($entries as $entry) {
            HelpEntry::create([
                'question' => $entry['question'],
                'answer' => $entry['answer'],
                'category' => $entry['category'],
                'slug' => \Illuminate\Support\Str::slug($entry['question']),
                'sort_order' => $entry['sort_order'],
                'is_active' => true,
            ]);
        }
    }
}
