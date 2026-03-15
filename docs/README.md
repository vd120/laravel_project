# Nexus Documentation

Complete documentation for the Nexus social media platform.

---

## Quick Start

| Document | Description |
|----------|-------------|
| [README](../README.md) | Installation & setup guide |
| [Features](FEATURES.md) | Platform features overview |
| [Performance](PERFORMANCE.md) | Performance optimizations |

---

## Technical Documentation

### System Architecture
- [Architecture](ARCHITECTURE.md) - System overview and structure
- [Frontend](FRONTEND.md) - Frontend architecture and components
- [Performance](PERFORMANCE.md) - Performance optimizations & best practices

### Features
- [Features](FEATURES.md) - Complete feature list
- [Authentication](AUTHENTICATION.md) - Auth system details
- [Multilingual](MULTILINGUAL.md) - Language support (EN/AR with RTL)

### API
- [API Reference](API.md) - REST API documentation

---

## Need Help?

- Check [README](../README.md) for installation
- Review [Troubleshooting](../README.md#troubleshooting) section
- Check [Performance](PERFORMANCE.md) for optimization tips
- Create an issue on GitHub

---

## Recent Updates

### March 2026
- **Performance Optimization** - 71% page size reduction
  - Moved 1,374 lines inline CSS to external files
  - Extracted mobile header styles (110 lines)
  - Fixed comment CSS/JS loading (was loading per comment!)
  - Optimized font weights (400, 600, 700 only)
  - Fixed cache-busting (now uses file hashing)
  - GSAP animations optimized with defer
  
- **Multilingual Support** - Enhanced RTL support
  - Session-based language persistence
  - Database storage for authenticated users
  - Browser language detection
  - RTL/LTR layout switching

### Documentation Improvements
- Added [PERFORMANCE.md](PERFORMANCE.md) - Complete optimization guide
- Updated [README.md](../README.md) - Better troubleshooting section
- Enhanced [AUTHENTICATION.md](AUTHENTICATION.md) - More examples
- Improved [FEATURES.md](FEATURES.md) - API endpoints added

---

<div align="center">

**Nexus Social Media Platform**

*Built with Laravel & Vue.js*

</div>
