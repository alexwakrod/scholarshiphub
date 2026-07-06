<template>
  <div class="relative inline-flex flex-col items-center gap-3">
    <div class="relative w-48 h-48">
      <canvas
        ref="canvasRef"
        :width="canvasSize"
        :height="canvasSize"
        class="block rounded-full cursor-crosshair border-2 border-gray-300 dark:border-white/20"
        @mousedown="startDrag"
        @mousemove="dragMove"
        @mouseup="stopDrag"
        @mouseleave="stopDrag"
        @touchstart.prevent="startTouch"
        @touchmove.prevent="touchMove"
        @touchend="stopDrag"
      ></canvas>
      <div
        v-if="selectedPosition"
        class="absolute w-4 h-4 rounded-full border-2 border-white shadow-lg pointer-events-none"
        :style="{ top: selectedPosition.y - 8 + 'px', left: selectedPosition.x - 8 + 'px', backgroundColor: selectedColor }"
      ></div>
    </div>
    <div class="flex items-center gap-2">
      <input
        type="text"
        :value="selectedColor"
        @input="updateFromHex"
        class="w-24 px-2 py-1 rounded-lg glass-input border border-gray-300 dark:border-white/20 text-gray-900 dark:text-white text-sm text-center"
        maxlength="7"
        placeholder="#FFFFFF"
      />
      <button
        v-for="swatch in presets"
        :key="swatch"
        @click="setColor(swatch)"
        class="w-6 h-6 rounded-full border border-gray-300 dark:border-white/20 hover:scale-110 transition-transform"
        :style="{ backgroundColor: swatch }"
      ></button>
    </div>
    <div class="flex items-center gap-2">
      <GlassInput v-model="rgb.r" type="number" min="0" max="255" class="w-16 text-xs" placeholder="R" />
      <GlassInput v-model="rgb.g" type="number" min="0" max="255" class="w-16 text-xs" placeholder="G" />
      <GlassInput v-model="rgb.b" type="number" min="0" max="255" class="w-16 text-xs" placeholder="B" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import GlassInput from './GlassInput.vue';

const props = defineProps({
  modelValue: { type: String, default: '#3B82F6' },
});
const emit = defineEmits(['update:modelValue']);

const canvasRef = ref(null);
const canvasSize = ref(256);
const selectedPosition = ref(null);
const selectedColor = ref(props.modelValue);
const rgb = ref({ r: 59, g: 130, b: 246 });
const dragging = ref(false);

const presets = ['#3B82F6', '#FFD700', '#10B981', '#EF4444', '#8B5CF6', '#F59E0B', '#EC4899', '#FFFFFF', '#000000'];

function hsvToRgb(h, s, v) {
  let r, g, b;
  const i = Math.floor(h * 6);
  const f = h * 6 - i;
  const p = v * (1 - s);
  const q = v * (1 - f * s);
  const t = v * (1 - (1 - f) * s);
  switch (i % 6) {
    case 0: r = v; g = t; b = p; break;
    case 1: r = q; g = v; b = p; break;
    case 2: r = p; g = v; b = t; break;
    case 3: r = p; g = q; b = v; break;
    case 4: r = t; g = p; b = v; break;
    case 5: r = v; g = p; b = q; break;
  }
  return { r: Math.round(r * 255), g: Math.round(g * 255), b: Math.round(b * 255) };
}

function componentToHex(c) { const hex = c.toString(16); return hex.length === 1 ? '0' + hex : hex; }
function rgbToHex(r, g, b) { return '#' + componentToHex(r) + componentToHex(g) + componentToHex(b); }
function hexToRgb(hex) {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? { r: parseInt(result[1], 16), g: parseInt(result[2], 16), b: parseInt(result[3], 16) } : { r: 0, g: 0, b: 0 };
}

function drawWheel() {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const w = canvas.width, h = canvas.height, radius = w / 2;
  for (let y = 0; y < h; y++) {
    for (let x = 0; x < w; x++) {
      const dx = x - radius, dy = y - radius;
      const distance = Math.sqrt(dx * dx + dy * dy);
      if (distance > radius) continue;
      const angle = Math.atan2(dy, dx) + Math.PI;
      const hue = angle / (2 * Math.PI);
      const saturation = distance / radius;
      const { r, g, b } = hsvToRgb(hue, saturation, 1);
      ctx.fillStyle = `rgb(${r},${g},${b})`;
      ctx.fillRect(x, y, 1, 1);
    }
  }
}

function getColorFromPosition(x, y) {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const rect = canvas.getBoundingClientRect();
  const scaleX = canvas.width / rect.width, scaleY = canvas.height / rect.height;
  const cx = (x - rect.left) * scaleX, cy = (y - rect.top) * scaleY;
  const radius = canvas.width / 2;
  const dx = cx - radius, dy = cy - radius;
  const distance = Math.sqrt(dx * dx + dy * dy);
  if (distance > radius) return null;
  const angle = Math.atan2(dy, dx) + Math.PI;
  const hue = angle / (2 * Math.PI);
  const saturation = distance / radius;
  const { r, g, b } = hsvToRgb(hue, saturation, 1);
  return { x: cx, y: cy, r, g, b, hex: rgbToHex(r, g, b) };
}

function updateColorFromPosition(pos) {
  if (!pos) return;
  selectedPosition.value = { x: pos.x, y: pos.y };
  selectedColor.value = pos.hex;
  rgb.value = { r: pos.r, g: pos.g, b: pos.b };
  emit('update:modelValue', pos.hex);
}

function startDrag(event) { dragging.value = true; const pos = getColorFromPosition(event.clientX, event.clientY); updateColorFromPosition(pos); }
function dragMove(event) { if (!dragging.value) return; const pos = getColorFromPosition(event.clientX, event.clientY); updateColorFromPosition(pos); }
function stopDrag() { dragging.value = false; }
function startTouch(event) { dragging.value = true; const touch = event.touches[0]; const pos = getColorFromPosition(touch.clientX, touch.clientY); updateColorFromPosition(pos); }
function touchMove(event) { if (!dragging.value) return; const touch = event.touches[0]; const pos = getColorFromPosition(touch.clientX, touch.clientY); updateColorFromPosition(pos); }

function updateFromHex(event) {
  let val = event.target.value;
  if (!val.startsWith('#')) val = '#' + val;
  if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
    selectedColor.value = val;
    const { r, g, b } = hexToRgb(val);
    rgb.value = { r, g, b };
    emit('update:modelValue', val);
  }
}

function setColor(color) { selectedColor.value = color; const { r, g, b } = hexToRgb(color); rgb.value = { r, g, b }; emit('update:modelValue', color); }

watch(() => props.modelValue, (val) => {
  if (val && val !== selectedColor.value) { selectedColor.value = val; const { r, g, b } = hexToRgb(val); rgb.value = { r, g, b }; }
});

onMounted(() => { nextTick(drawWheel); const { r, g, b } = hexToRgb(props.modelValue); rgb.value = { r, g, b }; });
</script>