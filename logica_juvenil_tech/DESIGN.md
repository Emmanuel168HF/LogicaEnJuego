---
name: Logica Juvenil Tech
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#45464f'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#767680'
  outline-variant: '#c6c5d0'
  surface-tint: '#4f5c8e'
  primary: '#000f3f'
  on-primary: '#ffffff'
  primary-container: '#172554'
  on-primary-container: '#808dc2'
  inverse-primary: '#b7c4fd'
  secondary: '#0051d5'
  on-secondary: '#ffffff'
  secondary-container: '#316bf3'
  on-secondary-container: '#fefcff'
  tertiary: '#1c0047'
  on-tertiary: '#ffffff'
  tertiary-container: '#35007a'
  on-tertiary-container: '#a375ff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dce1ff'
  primary-fixed-dim: '#b7c4fd'
  on-primary-fixed: '#071747'
  on-primary-fixed-variant: '#374475'
  secondary-fixed: '#dbe1ff'
  secondary-fixed-dim: '#b4c5ff'
  on-secondary-fixed: '#00174b'
  on-secondary-fixed-variant: '#003ea8'
  tertiary-fixed: '#eaddff'
  tertiary-fixed-dim: '#d2bbff'
  on-tertiary-fixed: '#25005a'
  on-tertiary-fixed-variant: '#5a00c6'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  display:
    fontFamily: Plus Jakarta Sans
    fontSize: 40px
    fontWeight: '800'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
rounded:
  sm: 0.5rem
  DEFAULT: 1rem
  md: 1.5rem
  lg: 2rem
  xl: 3rem
  full: 9999px
spacing:
  gutter: 1.5rem
  margin: 2.5rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style
The design system articulates an elevated, energetic, and highly readable educational environment tailored for students diving into programming and computational logic. It bridges the gap between serious academic discipline and lively, gamified visual culture. 

The aesthetic sits at the intersection of modern SaaS clarity and tactile EdTech engagement:
- **Atmosphere:** Clean, inviting, optimistic, and structured. Complex algorithmic concepts are translated into digestible, friendly interactive surfaces.
- **Personality:** Approachable mentor, precise, stimulating, and empowering.
- **Visual Mechanics:** Rich deep navy foundation anchors the interface, paired with vibrant cobalt action surfaces, playful violet discovery accents, and bright emerald positive reinforcement. Crisp pure white cards float effortlessly on cool porcelain ground layers, bounded by fine geometric outlines and pill-soft surfaces.

## Colors
The color architecture relies on high contrast and distinct semantic functional roles designed to guide cognitive load during problem-solving:

- **Deep Brand Anchor (`#172554`):** Represents structural authority, used for branding headers, global navigation bars, high-level module titles, and strong dark contextual accents.
- **Interactive Blue (`#2563EB`):** The primary engine of interactivity. Applied to primary call-to-actions, active navigation markers, radio/checkbox selections, focused inputs, and interactive dropzones.
- **Accent Violet (`#7C3AED`):** Signals exploration, gamification perks, diagnostic milestones, and secondary interactive states like badge rewards and category tags.
- **Success Emerald (`#22C55E`):** Reserved for validation, positive feedback dialogs, completed lessons, progress bars, and high achievement metrics.
- **Neutral Stack:**
  - Canvas / Page Ground: `#F8FAFC`
  - Surfaces / Card Backgrounds: `#FFFFFF`
  - Primary Typography: `#1E293B`
  - Secondary / Supporting Typography: `#64748B`
  - Subtle Structural Borders: `#E2E8F0`
  - Subtle Muted Ground (Inputs, disabled items): `#F1F5F9`
  - Critical / Error state: `#EF4444`

## Typography
Plus Jakarta Sans is utilized uniformly across all roles to project a clean, contemporary, and warmly geometric personality. Its subtle optical ink-traps and generous apertures deliver crisp reading comfort for both long instructional passages and quick algorithmic puzzle prompts.

- **Headlines & Display:** Set in bold and extrabold weights with tight negative letter tracking to retain solid visual momentum.
- **Body:** Open line heights (1.5x) in regular weight prevent reading fatigue during complex instructional logic steps.
- **Labels & Micro-copy:** Emphasize medium and semibold weights for instant UI scanning across module grids, quiz questions, progress percentages, and feedback banners.

## Layout & Spacing
The layout operates on a standard 12-column fixed desktop canvas centered at `1440px`, structured to support dual-column study layouts, card catalogs, and administrative sidebars:

- **Grid Structure:** 12 columns with `1.5rem` (`24px`) gutters and a minimum outer canvas margin of `2.5rem` (`40px`).
- **Vertical Rhythm:** Built strictly on an 8-point base increment. Nested card components utilize `space-md` (`16px`) to `space-lg` (`24px`) internal padding, while macro view containers utilize `space-xl` (`32px`) to `space-2xl` (`48px`).
- **View Distributions:**
  - *Catalog & Dashboard views:* 3 or 4 uniform card columns.
  - *Exercise & Question views:* Split 50/50 interactive workbench layout or 60/40 instruction-to-answer hierarchy.
  - *Administrative views:* 280px fixed navigation sidebar flanked by a dynamic content panel.

## Elevation & Depth
Depth in this system is light, aerial, and tactile. Instead of heavy structural borders or muddied dark drop shadows, the interface achieves depth through tinted ambient soft illumination and pure white surface containment over `#F8FAFC`:

- **Flat / Ground:** `background: #F8FAFC;`
- **Surface Elevation 1 (Resting Cards, Module Tiles, Inputs):**  
  `background: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0 1px 3px 0 rgba(23, 37, 84, 0.04), 0 1px 2px -1px rgba(23, 37, 84, 0.04);`
- **Surface Elevation 2 (Card Hover, Dropdown Menus, Sticky Navbar):**  
  `background: #FFFFFF; border: 1px solid #CBD5E1; box-shadow: 0 10px 15px -3px rgba(23, 37, 84, 0.07), 0 4px 6px -4px rgba(23, 37, 84, 0.04); transform: translateY(-2px);`
- **Surface Elevation 3 (Feedback Modals, Draggable Question Tokens in Flight):**  
  `box-shadow: 0 20px 25px -5px rgba(23, 37, 84, 0.10), 0 8px 10px -6px rgba(23, 37, 84, 0.05);`

## Shapes
A plush, modern rounded geometry defines the friendly visual identity of the platform:

- **Tokens & Interactive Pills:** Fully rounded `9999px` applied to filter chips, badges, difficulty indicators, progress pills, and primary buttons.
- **Interactive Option Rows & Inputs:** `1rem` (`16px`) corner radius gives interactive quiz rows a comfortable, clickable surface.
- **Cards, Panels & Containers:** `1.5rem` to `2rem` (`24px` to `32px`) roundedness creates distinct, soft modules that eliminate visual harshness.

## Components

### Buttons
- **Primary Action:** Solid Interactive Blue (`#2563EB`) background, pure white text (`#FFFFFF`), `12px 24px` padding, fully pill-rounded or `16px` border-radius. Active state features a subtle tactile depression (`translateY(1px)`).
- **Secondary / Ghost:** Outlined with `#E2E8F0` on white, text in `#172554`. On hover: border switches to `#2563EB` with `#EFF6FF` background tint.
- **Achievement / Submit:** Solid Emerald (`#22C55E`) with white text used exclusively for verifying answers and completing modules.

### Cards & Module Tiles
- White canvas surface bounded by `#E2E8F0` border, `24px` rounded radius, padded with `24px`.
- Headers integrate an icon badge, module level indicator, and progress ring or bar. Smooth elevation shift on hover.

### Quiz Options & Selection Rows
- Standard multi-choice items present full-width pill-shaped rows (`rounded-xl` / `16px`).
- Default: Border `#E2E8F0`, background `#FFFFFF`, text `#1E293B`.
- Selected: Border `#2563EB`, background `#EFF6FF`, text `#172554`.
- Correct / Incorrect Feedback: Emerald `#DCFCE7` with `#15803D` border; Red `#FEE2E2` with `#B91C1C` border.

### Interactive Drag & Drop Chips
- Draggable code elements and terms use solid white surfaces with `#E2E8F0` borders, `9999px` pill contours, and left grip handles.
- Dropzones have dashed `2px` borders in `#CBD5E1` with `#F8FAFC` background; upon drag hover, borders turn solid `#2563EB` over `#EFF6FF`.

### Form Inputs & Text Fields
- Height: `48px`, `12px` border-radius, background `#FFFFFF`, border `#E2E8F0`. Focused state displays a crisp 3px ring: `0 0 0 3px rgba(37, 99, 235, 0.15)`.

### Progress Bars
- Background track of `#F1F5F9`, rounded `9999px`. Filled progress indicator in `#22C55E` or `#2563EB` with animated transition width.