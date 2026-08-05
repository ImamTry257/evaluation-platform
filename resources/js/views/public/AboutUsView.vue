<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'

const scrolled = ref(false)

// const teamMembers = [
//   {
//     name: 'Dr. Ahmad Santoso',
//     role: 'Ketua Peneliti',
//     institution: 'Universitas Indonesia',
//     email: 'ahmad@pekls.ac.id',
//     badge: 'Project Leader',
//     hasBadge: true,
//     image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCNmtksYuft2A8zk2L9e4R1u4iBmAPS6xt5GzI9QQdF3-PIqklWefXhvj2GK6AuT5OhM2gQuQK6t6SOcRWDa5s35nB2I7QxbL94pxGqhJTxjWY1YpwbgoYIQZSgHQkozrjHM0iVk8VtXCJepmvcI6CLZ839kAJWldjb2Nqt0gvyS4CN_D_fbABrJq8pax6B3Wefz-lGESIN-SChmWqpjh1mG4crA6eWUjrxtIHsQAwcsOK-xXQ2fA',
//   },
//   {
//     name: 'Siti Aminah, M.T.',
//     role: 'Sistem Arsitek',
//     institution: 'Lembaga Riset Teknologi',
//     email: 'siti@pekls.ac.id',
//     image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCLYVhL5N2sGc0hk-BzT7u384LhUjibDPNmAwyuCZBVRTHuozJNfwnUcQIXRHXcVv87s0JmoCAcwlMdAHVD-jTwRzhTeov3AQa9piLnOtRk85ADHaRs5bgZMbz-zSRZv4W3E37fkDsJrp2oTtWMzTUCjfaTdSKcrzSslnHl7MnVkJNkgoHsIld4OaEo2BTeXFWC2IbooyZp368U32Sia_oGY9eLDI548XfVoYsRflwswSpNKJ3Ybg',
//   },
//   {
//     name: 'Budi Hartono, M.Sc',
//     role: 'Pakar Lingkungan',
//     institution: 'Institut Ekologi Nasional',
//     email: 'budi@pekls.ac.id',
//     image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCftTUzyyWp_BfmIdsEG6zuhOl5an2tz1oYsV39LgQyJckf-efctbX0TCsSGPAraVIWlVkv7jTFfPP9OcqnJGmX0ZZ7SIRQtirSbBYyP-SWfDOmhFtwBFwSQA3QM6CyvLTuafX8jfQAsEAdp8PBCTx6CZvSTb5pAGrVQYXxXxI7CpximnnhqHK222DgNWHtbHXW3gAU7GwGo1XKKXI1_mZNVqAJgJXTkxgcu8zOiF2NYCi4jwt-LQ',
//   },
// ]

const teamMembers = [
  { name: 'Prof. Dr. Edi Istiyono, M.Si' },
  { name: 'Aditya Yoga Purnama' },
  { name: 'Imam Try Wibowo' },
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
        <ul class="team-list fade-in-delay-2">
          <li
            v-for="(member, index) in teamMembers"
            :key="member.name"
            class="team-list-item"
          >
            <span class="team-number font-label-caps text-label-caps text-[#004592]">{{ index + 1 }}</span>
            <span class="team-member-name font-body-lg text-body-lg text-on-surface">{{ member.name }}</span>
          </li>
        </ul>
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
  box-shadow: 0 8px 30px rgba(0, 69, 146, 0.1);
  border-color: #004592;
}

/* ===== HEADER BUTTONS ===== */
.header-btn-rotate {
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  border-radius: 0.5rem;
  padding: 0.5rem;
}
.header-btn-rotate:hover {
  background-color: rgba(0, 69, 146, 0.1);
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
  background: linear-gradient(90deg, transparent, rgba(0, 69, 146, 0.05), transparent);
  transition: left 0.6s ease;
}
.profile-card:hover::before {
  left: 100%;
}
.profile-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 50px rgba(0, 69, 146, 0.15);
  border-color: #004592;
}
.profile-card:hover .profile-avatar {
  transform: scale(1.1);
  border-color: #004592;
  box-shadow: 0 8px 25px rgba(0, 69, 146, 0.3);
}
.profile-card:hover .profile-name {
  color: #004592;
}
.profile-card:hover .role-badge {
  transform: scale(1.1) translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 69, 146, 0.4);
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
  border-color: #004592;
  box-shadow: 0 0 0 4px rgba(0, 69, 146, 0.2);
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
  background: rgba(0, 69, 146, 0.1);
  border-radius: 0.5rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}
.mail-link:hover::before {
  opacity: 1;
}
.mail-link:hover {
  color: #004592;
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
  color: #004592;
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
  background: radial-gradient(circle, rgba(0, 69, 146, 0.1) 0%, transparent 70%);
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
  background: radial-gradient(circle, rgba(0, 69, 146, 0.03) 0%, transparent 70%);
  pointer-events: none;
}

.team-title {
  transition: all 0.3s ease;
}
.team-title:hover {
  color: #004592;
}

/* ===== TEAM LIST ===== */
.team-list {
  max-width: 640px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
}
.team-list-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e5e7eb;
  transition: all 0.3s ease;
  position: relative;
}
.team-list-item:first-child {
  border-top: 1px solid #e5e7eb;
}
.team-list-item:hover {
  background: rgba(0, 69, 146, 0.06);
  transform: translateX(6px);
}
.team-list-item:hover .team-member-name {
  color: #004592;
}
.team-number {
  min-width: 2rem;
  height: 2rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  background: rgba(0, 69, 146, 0.12);
  transition: all 0.3s ease;
}
.team-list-item:hover .team-number {
  background: #004592;
  color: #ffffff;
}
.team-member-name {
  transition: all 0.3s ease;
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
  background: linear-gradient(90deg, #004592, #2f6fed);
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
  color: #004592;
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
  background: linear-gradient(135deg, #004592, #2f6fed);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  transition: all 0.3s ease;
}
.gradient-text:hover {
  background: linear-gradient(135deg, #2f6fed, #004592);
  -webkit-background-clip: text;
  background-clip: text;
}

/* ===== PULSE RING ===== */
.pulse-ring {
  animation: pulseRing 2s ease-in-out infinite;
}
@keyframes pulseRing {
  0% { box-shadow: 0 0 0 0 rgba(0, 69, 146, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(0, 69, 146, 0); }
  100% { box-shadow: 0 0 0 0 rgba(0, 69, 146, 0); }
}

/* ===== FADE IN ANIMATIONS ===== */
.fade-in { animation: fadeIn 0.6s ease-out forwards; }
.fade-in-delay { animation: fadeIn 0.6s ease-out 0.15s forwards; opacity: 0; }
.fade-in-delay-2 { animation: fadeIn 0.6s ease-out 0.3s forwards; opacity: 0; }
.fade-in-delay-3 { animation: fadeIn 0.6s ease-out 0.45s forwards; opacity: 0; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>
