import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/* =============================================
   アニメーションシステム
   ============================================= */

/** スクロール登場 (Intersection Observer) */
function initReveal() {
    const els = document.querySelectorAll('.reveal, .reveal-left');
    if (!els.length) return;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
    els.forEach(el => observer.observe(el));
}

/** ヒーローセクション マウスパララックス */
function initHeroParallax() {
    const hero = document.getElementById('hero-section');
    if (!hero) return;
    const slow = hero.querySelector('.parallax-slow');
    const mid  = hero.querySelector('.parallax-mid');
    const fast = hero.querySelector('.parallax-fast');
    let ticking = false;
    hero.addEventListener('mousemove', e => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            const cx = (e.clientX / window.innerWidth  - 0.5) * 2;
            const cy = (e.clientY / window.innerHeight - 0.5) * 2;
            if (slow) slow.style.transform = `translate(${cx * -6}px, ${cy * -4}px)`;
            if (mid)  mid.style.transform  = `translate(${cx * -12}px, ${cy * -8}px)`;
            if (fast) fast.style.transform = `translate(${cx * -20}px, ${cy * -14}px)`;
            ticking = false;
        });
    });
    hero.addEventListener('mouseleave', () => {
        [slow, mid, fast].forEach(el => {
            if (el) el.style.transform = '';
        });
    });
}

/** 流れ星を定期的に発生 */
function launchShootingStar(container) {
    const star = document.createElement('div');
    star.className = 'shooting-star';
    const x = Math.random() * 70;
    const y = Math.random() * 35;
    star.style.left = x + '%';
    star.style.top  = y + '%';
    container.appendChild(star);
    star.addEventListener('animationend', () => star.remove());
}

function initShootingStars() {
    const container = document.getElementById('stars-container');
    if (!container) return;
    // 初回: 2秒後
    setTimeout(() => launchShootingStar(container), 2000);
    // 以降: 5〜12秒ごとにランダム発生
    function schedule() {
        const delay = 5000 + Math.random() * 7000;
        setTimeout(() => {
            launchShootingStar(container);
            schedule();
        }, delay);
    }
    schedule();
}

/** ページ読み込みフェードイン */
function initPageFade() {
    const main = document.querySelector('main');
    if (main) main.classList.add('page-content');
}

/** 数字カウントアップ */
window.countUp = (target, duration = 1200) => ({
    display: 0,
    start(end) {
        const step = end / (duration / 16);
        const timer = setInterval(() => {
            this.display = Math.min(this.display + step, end);
            if (this.display >= end) { this.display = end; clearInterval(timer); }
        }, 16);
    },
});

/** DOM ready で全て起動 */
document.addEventListener('DOMContentLoaded', () => {
    initReveal();
    initHeroParallax();
    initShootingStars();
    initPageFade();
});

/**
 * 空き状況カレンダーコンポーネント
 */
window.availabilityCalendar = (apiUrl, checkInInputId = null, checkOutInputId = null) => ({
    apiUrl,
    year:  new Date().getFullYear(),
    month: new Date().getMonth(), // 0-indexed
    bookedDates: [],
    blockedDates: [],
    blockoutReasons: {},
    priceMap: {},
    basePrice: 0,
    loading: false,
    // 日付選択モード
    selectMode: checkInInputId ? 'check_in' : null,
    hoverDay:   null,
    selectedIn:  null,
    selectedOut: null,

    get daysInMonth() {
        return new Date(this.year, this.month + 1, 0).getDate();
    },

    get startBlank() {
        return new Date(this.year, this.month, 1).getDay();
    },

    dateStr(day) {
        return `${this.year}-${String(this.month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    },

    isBooked(day) {
        return this.bookedDates.includes(this.dateStr(day));
    },

    isBlocked(day) {
        return this.blockedDates.includes(this.dateStr(day));
    },

    blockoutReason(day) {
        return this.blockoutReasons[this.dateStr(day)] ?? null;
    },

    isPast(day) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return new Date(this.year, this.month, day) < today;
    },

    isSelectable(day) {
        return !this.isPast(day) && !this.isBooked(day) && !this.isBlocked(day);
    },

    isSelectedIn(day)  { return this.selectedIn  && this.dateStr(day) === this.selectedIn; },
    isSelectedOut(day) { return this.selectedOut && this.dateStr(day) === this.selectedOut; },

    isInRange(day) {
        if (!this.selectedIn) return false;
        const end = this.selectedOut || (this.hoverDay ? this.dateStr(this.hoverDay) : null);
        if (!end) return false;
        return this.dateStr(day) > this.selectedIn && this.dateStr(day) < end;
    },

    dayPrice(day) {
        return this.priceMap[day]?.price ?? this.basePrice;
    },

    isSpecialPrice(day) {
        return this.priceMap[day]?.is_special ?? false;
    },

    clickDay(day) {
        if (!this.isSelectable(day) || !this.selectMode) return;
        const d = this.dateStr(day);

        if (this.selectMode === 'check_in' || !this.selectedIn || d <= this.selectedIn) {
            this.selectedIn  = d;
            this.selectedOut = null;
            this.selectMode  = 'check_out';
            if (checkInInputId) {
                const el = document.getElementById(checkInInputId);
                if (el) { el.value = d; el.dispatchEvent(new Event('input')); }
            }
        } else {
            // check_out を選択（チェックイン翌日以降のみ）
            if (d <= this.selectedIn) return;
            this.selectedOut = d;
            this.selectMode  = 'check_in'; // 次回リセット
            if (checkOutInputId) {
                const el = document.getElementById(checkOutInputId);
                if (el) { el.value = d; el.dispatchEvent(new Event('input')); }
            }
        }
    },

    async fetchBooked() {
        this.loading = true;
        try {
            const res  = await fetch(`${this.apiUrl}?year=${this.year}&month=${this.month + 1}`);
            const data = await res.json();
            if (Array.isArray(data)) {
                this.bookedDates = data;
            } else {
                this.bookedDates     = data.booked          ?? [];
                this.blockedDates    = data.blocked         ?? [];
                this.blockoutReasons = data.blockoutReasons ?? {};
                this.priceMap        = data.priceMap        ?? {};
                this.basePrice       = data.basePrice       ?? 0;
            }
        } finally {
            this.loading = false;
        }
    },

    prevMonth() {
        const today = new Date();
        if (this.year === today.getFullYear() && this.month === today.getMonth()) return;
        if (this.month === 0) { this.year--; this.month = 11; }
        else { this.month--; }
        this.fetchBooked();
    },

    nextMonth() {
        if (this.month === 11) { this.year++; this.month = 0; }
        else { this.month++; }
        this.fetchBooked();
    },

    get canGoPrev() {
        const today = new Date();
        return !(this.year === today.getFullYear() && this.month === today.getMonth());
    },
});

/**
 * 予約フォーム (日付自動補正 + 価格動的計算)
 */
window.reservationForm = (pricePerNight, initCheckIn = '', initCheckOut = '', initGuests = 1) => ({
    pricePerNight,
    checkIn:  initCheckIn,
    checkOut: initCheckOut,
    guests:   initGuests,

    get minCheckOut() {
        if (!this.checkIn) return '';
        const d = new Date(this.checkIn);
        d.setDate(d.getDate() + 1);
        return d.toISOString().slice(0, 10);
    },

    get nights() {
        if (!this.checkIn || !this.checkOut) return 0;
        const diff = (new Date(this.checkOut) - new Date(this.checkIn)) / 86400000;
        return diff > 0 ? diff : 0;
    },

    get totalPrice() {
        return this.pricePerNight * this.nights;
    },

    onCheckInChange() {
        // チェックアウトがチェックインより前になる場合は翌日に自動修正
        if (this.checkOut && this.checkOut <= this.checkIn) {
            const d = new Date(this.checkIn);
            d.setDate(d.getDate() + 1);
            this.checkOut = d.toISOString().slice(0, 10);
        }
    },
});

/**
 * お気に入りトグルコンポーネント
 */
window.favoriteToggle = (apiUrl, initFavorited = false) => ({
    apiUrl,
    favorited: initFavorited,

    async toggle() {
        const res = await fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        if (res.ok) {
            const data = await res.json();
            this.favorited = data.favorited;
        }
    },
});

/**
 * 周辺スポットウィジェット (Overpass API プロキシ)
 */
window.nearbySpots = (lat, lng) => ({
    lat, lng,
    loading: true,
    error: false,
    spots: [],
    activeCategory: 'all',

    get categories() {
        const seen = new Set();
        this.spots.forEach(s => seen.add(s.category));
        return ['all', ...seen];
    },

    get filteredSpots() {
        if (this.activeCategory === 'all') return this.spots;
        return this.spots.filter(s => s.category === this.activeCategory);
    },

    categoryLabel(cat) {
        const labels = {
            all: 'すべて', peak: '山頂・登山', viewpoint: '展望', attraction: '観光',
            waterfall: '滝', hot_spring: '温泉', camp_site: 'キャンプ', museum: '博物館',
            park: '公園', beach: 'ビーチ', other: 'その他',
        };
        return labels[cat] ?? cat;
    },

    async load() {
        try {
            const res  = await fetch(`/api/nearby-spots?lat=${this.lat}&lng=${this.lng}`);
            const data = await res.json();
            if (data.error || !data.spots) { this.error = true; }
            else { this.spots = data.spots; }
        } catch { this.error = true; }
        finally { this.loading = false; }
    },
});

Alpine.start();
