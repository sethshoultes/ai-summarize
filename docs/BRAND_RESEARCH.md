# AI Service Brand Research for WordPress Plugin

## Overview

This document compiles brand guidelines, color specifications, and usage requirements for the 5 AI services that will be integrated into the AI Summarize WordPress plugin. This research ensures brand compliance and proper visual representation for each service.

## 1. ChatGPT / OpenAI

### Brand Identity
- **Company**: OpenAI
- **Product**: ChatGPT
- **Logo**: "Blossom" - Three intertwined triangles forming a flower-like shape
- **Current Status**: 2024-2025 brand refresh completed

### Official Brand Guidelines
- **URL**: https://openai.com/brand/
- **Guidelines**: OpenAI provides comprehensive brand guidelines for third-party usage
- **Key Requirement**: Prefer "Powered by OpenAI" badge over direct model names in titles

### Visual Identity
- **Logo Design**: The "blossom" logo features three intertwined triangles with refined geometry
- **Typography**: OpenAI Sans (custom font family)
  - Available weights: Light, Regular, Medium, Semibold, Bold
  - Each weight includes italic variants
- **Wordmark**: Features perfect circle "O" with fixed proportions

### Color Palette
- **Base Colors**: Greys and blues
- **Accent Colors**: Primary contrasting colors (green often associated with ChatGPT)
- **Theme**: Green/black/white scheme familiar from current ChatGPT interface

### Usage Guidelines
- Must adhere to official brand guidelines
- Cannot feature OpenAI marks more prominently than company name
- Use "Powered by OpenAI" when leveraging APIs
- Avoid model names in app titles

### Technical Implementation Notes
- ChatGPT doesn't have a separate standalone logo - uses OpenAI branding
- Green color distinguishes ChatGPT from other OpenAI products (DALL·E uses vibrant colors)
- Logo assets available through official brand guidelines

### URL Integration & Prompt Handling
- **Base URL**: `https://chat.openai.com/` or `https://chatgpt.com/`
- **Query Parameter**: `?q=` followed by URL-encoded prompt content
- **Model Selection**: Optional `?model=gpt-4&q=` or `?model=gpt-3.5&q=` parameters
- **Alternative Parameter**: Some extensions support `?prompt=` parameter
- **Limitations**:
  - User must be logged in to ChatGPT
  - UX shows empty chat for ~4 seconds before processing URL query
  - URL length limitations apply
- **Implementation Example**: `https://chat.openai.com/?q=Please%20summarize%20this%20article%3A%20[CONTENT]`
- **Content Handling**: Blog post content and custom prompts must be URL-encoded and concatenated

---

## 2. Perplexity AI

### Brand Identity
- **Company**: Perplexity AI
- **Logo**: Geometric outlined rectangles resembling open book/rotating doors
- **Brand Evolution**: Major redesign in 2023 by Smith & Diction

### Visual Identity
- **Logo Design**:
  - Stylized book with pages fanned 360 degrees
  - Symbolizes access to multiple information sources
  - Abstract geometric shape with outlined rectangles
- **Typography**: FK Grotesk typeface family
  - Chosen for international language support and readability
  - Fits "Scandinavian subway system" aesthetic

### Color Palette
- **Primary**: Turquoise/sea-blue tones
- **Light Shade**: Creativity and progress
- **Dark Shade**: Excellence and professionalism
- **Contrast**: Light and dark turquoise create visual interest

### Brand Philosophy
- **Aesthetic**: "Scandinavian subway system - clean and considered but invisible"
- **Approach**: Minimal, functional design
- **Goal**: Invisible brand that doesn't interfere with user experience

### Resources
- **Guidelines**: Available at brandingstyleguides.com and standards.site
- **Brand Kit**: Online brand guidelines accessible

### Technical Implementation Notes
- Focus on clean, minimal button design
- Use turquoise color palette for brand recognition
- Emphasize functionality over flashy design

### URL Integration & Prompt Handling
- **Base URL**: `https://perplexity.ai/`
- **Query Parameter**: `?q=` followed by URL-encoded prompt content
- **API Alternative**: REST API at `https://api.perplexity.ai/chat/completions`
- **Limitations**:
  - No direct URL parameter passing confirmed for web interface
  - API requires authentication with API key
  - Web interface may require user login
- **API Structure**: Uses OpenAI-compatible format with POST requests
- **Implementation Approach**: Direct URL linking may be limited; API integration preferred
- **Content Handling**: For web URLs, use `https://perplexity.ai/?q=Please%20summarize%3A%20[URL]`

---

## 3. Claude / Anthropic

### Brand Identity
- **Company**: Anthropic
- **Product**: Claude
- **Logo**: Abstract starburst/pinwheel suggesting ideas radiating outward
- **Brand Focus**: Trust, transparency, human-centric design

### Visual Identity
- **Logo Design**:
  - Clean, modern typeface with rounded, humanistic feel
  - Abstract starburst icon (not literal "C")
  - Understated and accessible design
- **Typography**:
  - Custom typeface: __copernicus_669e4a
  - Fallback: __copernicus_Fallback_669e4a
  - Additional palette: Styrene family + Klim's Tiempos family

### Color Palette
- **Primary**: Crail (#C15F3C) - warm rust-orange
- **Supporting Colors**:
  - Cloudy (#B1ADA1) - neutral grey
  - Pampas (#F4F3EE) - off-white
  - White (#FFFFFF)

### Brand Philosophy
- **Trust & Transparency**: Clean design reflects commitment to AI transparency
- **Human-Centric**: Soft, rounded edges suggest empathy and approachability
- **Professional**: Warm colors evoke calmness and intellectual depth

### Usage Guidelines
- **Background**: Logo designed for light or neutral backgrounds
- **Proportions**: Don't stretch or skew the logo
- **Spacing**: Allow plenty of padding around logo
- **Assets**: Downloadable press kit available in Anthropic Newsroom

### Technical Implementation Notes
- Warm rust-orange (#C15F3C) as primary brand color
- Avoid high-contrast neon or techy gradients
- Emphasize grounded, professional appearance

### URL Integration & Prompt Handling
- **Base URL**: `https://claude.ai/chat`
- **Query Parameter**: `?q=` or `?prompt=` followed by URL-encoded content
- **Limitations**:
  - User must be logged in to Claude
  - URL parameter length restrictions apply
  - May require user to confirm/send the pre-populated prompt
- **Implementation Example**: `https://claude.ai/chat?q=Please%20summarize%20this%20blog%20post%3A%20[CONTENT]`
- **Content Handling**: Blog post content and custom prompt templates can be URL-encoded together
- **User Experience**: Pre-populates the chat input field with the provided prompt

---

## 4. Microsoft Copilot

### Brand Identity
- **Company**: Microsoft
- **Product**: Copilot
- **Logo**: Abstract volumetric emblem with gradient multicolor palette
- **Evolution**: Major redesign November 2023

### Visual Identity
- **Logo Design**:
  - Hexagon with wavy edges and wide ribbon
  - No lettering - purely abstract emblem
  - Volumetric 3D appearance
- **Typography**: Clean, sans-serif style for readability
- **Design Elements**: Abstract shapes symbolizing assistance, guidance, connectivity

### Color Palette
- **Microsoft Brand Colors**: Red, blue, green, yellow
- **Gradient Range**: Orange to purple
- **Distribution**:
  - Top: Cold tones (blue, purple)
  - Bottom: Warm tones (orange-red)
- **Primary Association**: Blue & White (trust, intelligence, innovation)

### Brand Philosophy
- **AI Integration**: Seamless integration with Microsoft ecosystem
- **Adaptability**: Gradient effects show AI's dynamic nature
- **Trust**: Blue represents reliability and innovation

### Legal Considerations
- **Proprietary**: Microsoft brand assets require licensing
- **Usage**: Many uses need official license first
- **Guidelines**: Available through Microsoft legal trademark page

### Technical Implementation Notes
- Use Microsoft's signature blue as primary color
- Incorporate gradient effects when possible
- Maintain connection to broader Microsoft visual identity

### URL Integration & Prompt Handling
- **Base URL**: `https://copilot.microsoft.com/`
- **Query Parameters**: Limited documented support for direct URL parameters
- **Deep Links**: Uses base64-encoded data for complex prompts (not publicly documented)
- **Limitations**:
  - No official API for direct prompt passing via URL
  - Deep link functionality exists but requires encoding
  - User must be logged in to Microsoft account
  - Microsoft recommends using Microsoft Copilot Studio for programmatic access
- **Alternative Approach**: Microsoft Copilot Studio allows URL parameters like `?variable1=value1&variable2=value2`
- **Implementation Challenge**: Direct URL prompt passing not straightforward; may require custom Microsoft integration
- **Content Handling**: Limited to conversation sharing links rather than prompt pre-population

---

## 5. You.com

### Brand Identity
- **Company**: You.com
- **Logo**: Hexagonal emblem with star-like "glint" symbol
- **Brand Status**: Recent rebrand in 2024

### Visual Identity
- **Logo Design**:
  - Hexagonal emblem with central "glint" (star symbol)
  - Represents reflective and responsible AI use
  - Conveys precision and sophistication
- **Typography**:
  - Custom "Lumen" type system
  - "Ode to references of light, bringing focus and clarity"
- **Previous Design**: Friendly "YOU" with magnifying glass "Y"

### Color Palette
- **Current**: Deep blue gradient (light to dark)
- **Previous**: Bright blue (replaced in rebrand)
- **Theme**: Blue tones maintaining trust and modernity

### Brand Philosophy
- **Glint Symbol**: Represents reflective nature of AI and responsibility
- **Focus**: Understanding user intent and providing personalized responses
- **Evolution**: Contemporary and timeless design approach

### Resources
- **Official Info**: Available at you.com/articles/our-rebrand
- **Guidelines**: Limited public brand guidelines available
- **Contact**: May need direct contact for complete usage guidelines

### Technical Implementation Notes
- Use deep blue gradient for brand recognition
- Hexagonal design elements could complement button styling
- Emphasize precision and sophistication in visual treatment

### URL Integration & Prompt Handling
- **Base URL**: `https://you.com/search`
- **Query Parameters**:
  - `?q=` - Main search query parameter
  - `?chatMode=default` - Enables AI chat mode
  - `?chatMode=smart_routing` - Alternative chat mode
  - `?tbm=youchat` - Specifies You Chat functionality
  - `?fromSearchBar=true` - Additional context parameter
- **Implementation Example**: `https://you.com/search?q=Please%20summarize%20this%20article&chatMode=default&tbm=youchat`
- **API Alternative**: Web Search API available through you.com/platform
- **Content Handling**: Direct URL parameters work well for sending prompts and content
- **User Experience**: Opens directly in chat mode with search/AI response functionality

---

## Plugin Implementation Guidelines

### General Requirements
1. **Brand Compliance**: Ensure all representations follow official guidelines
2. **Consistency**: Maintain visual harmony between all 5 services
3. **Accessibility**: Meet WCAG 2.1 AA standards for all button designs
4. **Responsive**: Ensure proper display across all screen sizes
5. **Performance**: Optimize assets for fast loading

### Color Accessibility
- Ensure sufficient contrast ratios for all color combinations
- Provide alternative text for logo images
- Test with color blindness simulation tools

### Asset Management
- Store all brand assets in organized directory structure
- Use SVG format when possible for scalability
- Maintain separate light/dark theme variants if needed
- Keep original source files and optimized web versions

### Legal Compliance
- Review each service's specific usage terms
- Include proper attribution where required
- Stay updated on brand guideline changes
- Document usage permissions and limitations

### Technical Considerations
- Implement CSS custom properties for easy color management
- Use semantic HTML for better accessibility
- Optimize images and use appropriate formats
- Consider preloading critical brand assets

---

## Next Steps for Plugin Development

### Immediate Actions
1. Download official brand assets from each service
2. Create CSS variables for all brand colors
3. Design button layouts that accommodate all 5 services
4. Test color combinations for accessibility compliance

### Design Decisions Needed
1. Button size variations (small, medium, large)
2. Layout options (horizontal, vertical, grid)
3. Text label requirements and positioning
4. Hover state designs for each brand
5. Mobile-responsive breakpoint handling

### Asset Requirements
- Logo files in multiple formats (SVG, PNG)
- Color swatches and hex codes
- Typography guidelines and fallbacks
- Usage examples and mockups
- Legal compliance documentation

---

## Technical Implementation Summary

### URL Generation Strategy

Based on the research, here's how the plugin will handle prompt and content delivery:

#### 1. Prompt Template Processing
```php
// Example prompt template processing
$template = "Please summarize this blog post from [[WEBSITE]]: [[TITLE]]. Visit [[URL]] for the full content. [[CUSTOM_PROMPT]]";

$processed_prompt = str_replace([
    '[[WEBSITE]]',
    '[[TITLE]]',
    '[[URL]]',
    '[[CUSTOM_PROMPT]]'
], [
    get_bloginfo('name'),
    get_the_title(),
    get_permalink(),
    $user_custom_prompt
], $template);
```

#### 2. Service-Specific URL Generation
```php
$service_urls = [
    'chatgpt' => 'https://chat.openai.com/?q=' . urlencode($processed_prompt),
    'perplexity' => 'https://perplexity.ai/?q=' . urlencode($processed_prompt),
    'claude' => 'https://claude.ai/chat?q=' . urlencode($processed_prompt),
    'copilot' => 'https://copilot.microsoft.com/', // Limited URL parameter support
    'you' => 'https://you.com/search?q=' . urlencode($processed_prompt) . '&chatMode=default&tbm=youchat'
];
```

#### 3. Content Delivery Methods

**Method 1: URL + Content Reference**
- Send blog post URL in prompt for services to fetch content
- Most reliable across all services
- Requires services to access external URLs

**Method 2: Full Content Embedding**
- Include blog post content directly in URL parameters
- Limited by URL length restrictions (typically ~2000 characters)
- Better for shorter posts or excerpts

**Method 3: Hybrid Approach**
- Use URL reference as primary method
- Include excerpt/summary as fallback
- Provide both URL and key content snippets

### Security and URL Handling

#### URL Encoding and Sanitization
```php
// Sanitize and encode content for URL parameters
$safe_content = wp_kses($content, []);  // Strip all HTML
$safe_content = wp_strip_all_tags($safe_content);
$safe_content = substr($safe_content, 0, 1500);  // Limit length
$encoded_content = urlencode($safe_content);
```

#### Service Validation
```php
// Validate AI service URLs against whitelist
$allowed_domains = [
    'chat.openai.com',
    'chatgpt.com',
    'perplexity.ai',
    'claude.ai',
    'copilot.microsoft.com',
    'you.com'
];
```

### Plugin Implementation Considerations

#### 1. URL Length Management
- Monitor total URL length (stay under 2000 characters)
- Implement content truncation with ellipsis
- Prioritize most important content (title, first paragraph)

#### 2. Fallback Strategies
- For Copilot: Direct link to service with instruction to paste content
- For services with URL limits: Provide shortened prompts
- Include manual copy-paste instructions as backup

#### 3. User Experience Optimization
- Open links in new tab/window to preserve WordPress site
- Add loading states while generating URLs
- Provide clear instructions for each service's requirements

#### 4. Performance Considerations
- Cache generated URLs for repeated requests
- Lazy load service buttons to improve page speed
- Minimize JavaScript overhead for URL generation

This technical foundation ensures the plugin can effectively deliver blog post content and custom prompts to each AI service while respecting their individual URL parameter limitations and user experience patterns.