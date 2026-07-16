<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const scrolled = ref(false)

const teamMembers = [
  {
    name: 'Dr. Ahmad Santoso',
    role: 'Ketua Peneliti',
    institution: 'Universitas Indonesia',
    email: 'ahmad@pekls.ac.id',
    badge: 'Project Leader',
    hasBadge: true,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCNmtksYuft2A8zk2L9e4R1u4iBmAPS6xt5GzI9QQdF3-PIqklWefXhvj2GK6AuT5OhM2gQuQK6t6SOcRWDa5s35nB2I7QxbL94pxGqhJTxjWY1YpwbgoYIQZSgHQkozrjHM0iVk8VtXCJepmvcI6CLZ839kAJWldjb2Nqt0gvyS4CN_D_fbABrJq8pax6B3Wefz-lGESIN-SChmWqpjh1mG4crA6eWUjrxtIHsQAwcsOK-xXQ2fA',
  },
  {
    name: 'Siti Aminah, M.T.',
    role: 'Sistem Arsitek',
    institution: 'Lembaga Riset Teknologi',
    email: 'siti@pekls.ac.id',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCLYVhL5N2sGc0hk-BzT7u384LhUjibDPNmAwyuCZBVRTHuozJNfwnUcQIXRHXcVv87s0JmoCAcwlMdAHVD-jTwRzhTeov3AQa9piLnOtRk85ADHaRs5bgZMbz-zSRZv4W3E37fkDsJrp2oTtWMzTUCjfaTdSKcrzSslnHl7MnVkJNkgoHsIld4OaEo2BTeXFWC2IbooyZp368U32Sia_oGY9eLDI548XfVoYsRflwswSpNKJ3Ybg',
  },
  {
    name: 'Budi Hartono, M.Sc',
    role: 'Pakar Lingkungan',
    institution: 'Institut Ekologi Nasional',
    email: 'budi@pekls.ac.id',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCftTUzyyWp_BfmIdsEG6zuhOl5an2tz1oYsV39LgQyJckf-efctbX0TCsSGPAraVIWlVkv7jTFfPP9OcqnJGmX0ZZ7SIRQtirSbBYyP-SWfDOmhFtwBFwSQA3QM6CyvLTuafX8jfQAsEAdp8PBCTx6CZvSTb5pAGrVQYXxXxI7CpximnnhqHK222DgNWHtbHXW3gAU7GwGo1XKKXI1_mZNVqAJgJXTkxgcu8zOiF2NYCi4jwt-LQ',
  },
]

function handleScroll() {
  scrolled.value = window.scrollY > 50
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="text-on-surface flex flex-col min-h-screen">

    <main class="flex-grow pt-32 pb-12 max-w-[1440px] mx-auto px-8 w-full">
      <!-- Development Team -->
      <section class="team-section mb-12">
        <h2 class="team-title font-headline-lg text-headline-lg mb-8 text-center fade-in-delay border-animated pb-2">Development Team</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="(member, index) in teamMembers"
            :key="member.name"
            class="profile-card bg-white p-6 rounded-xl border border-outline-variant flex flex-col items-center text-center relative glass-card"
            :class="[
              index === 0 ? 'fade-in' : index === 1 ? 'fade-in-delay' : 'fade-in-delay-2'
            ]"
          >
            <div v-if="member.hasBadge" class="absolute top-4 right-4">
              <span class="role-badge bg-primary-container text-on-primary-container text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider pulse-ring">
                {{ member.badge }}
              </span>
            </div>
            <div class="profile-image-container mb-4">
              <div class="profile-avatar profile-ring w-24 h-24 rounded-full overflow-hidden border-2 border-primary/20">
                <img class="w-full h-full object-cover" :alt="member.name" :src="member.image" />
              </div>
            </div>
            <div class="profile-info">
              <h4 class="profile-name font-title-md text-title-md text-on-surface mb-1">{{ member.name }}</h4>
              <p class="font-label-caps text-label-caps text-primary mb-1 uppercase">{{ member.role }}</p>
              <p class="font-body-sm text-body-sm text-on-surface-variant mb-4">{{ member.institution }}</p>
            </div>
            <a
              class="mail-link social-link inline-flex items-center gap-1 font-body-sm text-body-sm text-primary"
              :href="'mailto:' + member.email"
            >
              <span class="material-symbols-outlined text-[18px]">mail</span> {{ member.email }}
            </a>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
/* ===== GLASS CARD ===== */
.glass-card {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(229, 231, 235, 0.5);
  transition: all 0.3s ease;
}
.glass-card:hover {
  box-shadow: 0 8px 30px rgba(0, 108, 73, 0.1);
  border-color: #10b981;
}

/* ===== HEADER BUTTONS ===== */
.header-btn-rotate {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  border-radius: 0.5rem;
  padding: 0.5rem;
}
.header-btn-rotate:hover {
  background-color: rgba(16, 185, 129, 0.1);
  transform: rotate(15deg) scale(1.1);
}
.header-btn-rotate:active {
  transform: rotate(0deg) scale(0.95);
}

/* ===== PROFILE CARD ===== */
.profile-card {
  transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
  overflow: hidden;
}
.profile-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.05), transparent);
  transition: left 0.6s ease;
}
.profile-card:hover::before {
  left: 100%;
}
.profile-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 50px rgba(0, 108, 73, 0.15);
  border-color: #10b981;
}
.profile-card:hover .profile-avatar {
  transform: scale(1.1);
  border-color: #10b981;
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}
.profile-card:hover .profile-name {
  color: #006c49;
}
.profile-card:hover .role-badge {
  transform: scale(1.1) translateY(-2px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}
.profile-avatar {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.profile-name {
  transition: all 0.3s ease;
}
.profile-image-container {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.profile-card:hover .profile-image-container {
  transform: scale(1.05);
}
.profile-ring {
  transition: all 0.4s ease;
}
.profile-card:hover .profile-ring {
  border-color: #10b981;
  box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
}
.profile-info {
  transition: all 0.3s ease;
}
.profile-card:hover .profile-info {
  transform: translateY(2px);
}

/* ===== ROLE BADGE ===== */
.role-badge {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* ===== MAIL LINK ===== */
.mail-link {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  position: relative;
  padding: 0.25rem 0.5rem;
  border-radius: 0.5rem;
}
.mail-link::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(16, 185, 129, 0.1);
  border-radius: 0.5rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}
.mail-link:hover::before {
  opacity: 1;
}
.mail-link:hover {
  color: #006c49;
  transform: translateX(4px);
}
.mail-link:hover .material-symbols-outlined {
  animation: mailBounce 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes mailBounce {
  0%, 100% { transform: scale(1) rotate(0deg); }
  25% { transform: scale(1.2) rotate(-5deg); }
  50% { transform: scale(1.3) rotate(5deg); }
  75% { transform: scale(1.1) rotate(-3deg); }
}

/* ===== SOCIAL LINK ===== */
.social-link {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.social-link:hover {
  transform: translateY(-3px);
  color: #006c49;
}

/* ===== ICON HOVER ===== */
.icon-hover {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.icon-hover:hover {
  transform: scale(1.2) rotate(5deg);
}

/* ===== HERO SECTION ===== */
.hero-section {
  position: relative;
  overflow: hidden;
}
.hero-section::before {
  content: '';
  position: absolute;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
  top: -100px;
  right: -100px;
  border-radius: 50%;
  animation: heroFloat 8s ease-in-out infinite;
}
.hero-section::after {
  content: '';
  position: absolute;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(139, 92, 246, 0.08) 0%, transparent 70%);
  bottom: -50px;
  left: -50px;
  border-radius: 50%;
  animation: heroFloat 10s ease-in-out infinite reverse;
}
@keyframes heroFloat {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(20px, -20px) scale(1.1); }
}

.hero-title {
  transition: all 0.4s ease;
}
.hero-title:hover {
  letter-spacing: 0.02em;
}

.hero-subtitle {
  transition: all 0.3s ease;
}
.hero-subtitle:hover {
  color: #161d19;
}

/* ===== TEAM SECTION ===== */
.team-section {
  position: relative;
}
.team-section::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 80%;
  height: 80%;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.03) 0%, transparent 70%);
  pointer-events: none;
}

.team-title {
  transition: all 0.3s ease;
}
.team-title:hover {
  color: #006c49;
}

/* ===== BORDER ANIMATION ===== */
.border-animated {
  position: relative;
}
.border-animated::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: linear-gradient(90deg, #006c49, #10b981);
  transition: width 0.4s ease;
}
.border-animated:hover::after {
  width: 100%;
}

/* ===== INSTITUTION NAME ===== */
.institution-name {
  transition: all 0.3s ease;
}
.institution-name:hover {
  color: #006c49;
}

/* ===== FOOTER ===== */
.footer-section {
  transition: all 0.4s ease;
}
.footer-section:hover {
  background-color: #e8f0e9;
}

/* ===== GRADIENT TEXT ===== */
.gradient-text {
  background: linear-gradient(135deg, #006c49, #10b981);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  transition: all 0.3s ease;
}
.gradient-text:hover {
  background: linear-gradient(135deg, #10b981, #006c49);
  -webkit-background-clip: text;
  background-clip: text;
}

/* ===== PULSE RING ===== */
.pulse-ring {
  animation: pulseRing 2s ease-in-out infinite;
}
@keyframes pulseRing {
  0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
  100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* ===== FADE IN ANIMATIONS ===== */
.fade-in { animation: fadeIn 0.6s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.6s ease-out 0.15s forwards; opacity: 0; }
.fade-in-delay-2 { animation: fadeIn 0.6s ease-out 0.3s forwards; opacity: 0; }
.fade-in-delay-3 { animation: fadeIn 0.6s ease-out 0.45s forwards; opacity: 0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>
