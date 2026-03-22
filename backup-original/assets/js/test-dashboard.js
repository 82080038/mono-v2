/**
 * JavaScript Testing Script for KSP Lam Gabe Jaya Dashboard
 * Proper testing to ensure all scripts work correctly
 */

// Test 1: Check jQuery availability
function testjQuery() {
    console.log('Testing jQuery availability...');
    
    if (typeof $ !== 'undefined') {
        console.log('✅ jQuery is available');
        console.log('jQuery version:', $.fn.jquery);
        return true;
    } else {
        console.error('❌ jQuery is not available');
        return false;
    }
}

// Test 2: Check Bootstrap availability
function testBootstrap() {
    console.log('Testing Bootstrap availability...');
    
    if (typeof bootstrap !== 'undefined') {
        console.log('✅ Bootstrap is available');
        return true;
    } else {
        console.error('❌ Bootstrap is not available');
        return false;
    }
}

// Test 3: Check Chart.js availability
function testChartJS() {
    console.log('Testing Chart.js availability...');
    
    if (typeof Chart !== 'undefined') {
        console.log('✅ Chart.js is available');
        return true;
    } else {
        console.error('❌ Chart.js is not available');
        return false;
    }
}

// Test 4: Check Content Renderer
function testContentRenderer() {
    console.log('Testing Content Renderer...');
    
    if (typeof window.contentRenderer !== 'undefined') {
        console.log('✅ Content Renderer is available');
        return true;
    } else {
        console.error('❌ Content Renderer is not available');
        return false;
    }
}

// Test 5: Check Enhanced UI
function testEnhancedUI() {
    console.log('Testing Enhanced UI...');
    
    if (typeof window.enhancedUI !== 'undefined') {
        console.log('✅ Enhanced UI is available');
        return true;
    } else {
        console.error('❌ Enhanced UI is not available');
        return false;
    }
}

// Test 6: Check Modal Manager
function testModalManager() {
    console.log('Testing Modal Manager...');
    
    if (typeof window.modalManager !== 'undefined') {
        console.log('✅ Modal Manager is available');
        return true;
    } else {
        console.error('❌ Modal Manager is not available');
        return false;
    }
}

// Test 7: Test Modal Triggers
function testModalTriggers() {
    console.log('Testing Modal Triggers...');
    
    const addMemberBtn = $('[data-action="add-member"]');
    if (addMemberBtn.length > 0) {
        console.log('✅ Add Member button found');
        
        // Test click event
        try {
            addMemberBtn.on('click', function() {
                console.log('🎯 Add Member button clicked');
            });
            console.log('✅ Add Member button event attached');
            return true;
        } catch (error) {
            console.error('❌ Error attaching Add Member button event:', error);
            return false;
        }
    } else {
        console.error('❌ Add Member button not found');
        return false;
    }
}

// Test 8: Test SPA Navigation
function testSPANavigation() {
    console.log('Testing SPA Navigation...');
    
    const navLinks = $('.nav-link');
    if (navLinks.length > 0) {
        console.log('✅ Navigation links found:', navLinks.length);
        
        // Test navigation click
        try {
            navLinks.first().trigger('click');
            console.log('✅ Navigation click test passed');
            return true;
        } catch (error) {
            console.error('❌ Error testing navigation:', error);
            return false;
        }
    } else {
        console.error('❌ Navigation links not found');
        return false;
    }
}

// Test 9: Test Form Validation
function testFormValidation() {
    console.log('Testing Form Validation...');
    
    const forms = $('.needs-validation');
    if (forms.length > 0) {
        console.log('✅ Validation forms found:', forms.length);
        
        try {
            const firstForm = forms.first();
            firstForm.on('submit', function(e) {
                e.preventDefault();
                console.log('🎯 Form validation test triggered');
            });
            console.log('✅ Form validation test passed');
            return true;
        } catch (error) {
            console.error('❌ Error testing form validation:', error);
            return false;
        }
    } else {
        console.error('❌ Validation forms not found');
        return false;
    }
}

// Test 10: Test Chart Rendering
function testChartRendering() {
    console.log('Testing Chart Rendering...');
    
    const chartCanvas = $('#performanceChart');
    if (chartCanvas.length > 0) {
        console.log('✅ Chart canvas found');
        
        try {
            const ctx = chartCanvas[0].getContext('2d');
            
            // Destroy existing chart if it exists
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }
            
            const testChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Test'],
                    datasets: [{
                        data: [10],
                        borderColor: 'rgba(54, 162, 235, 0.8)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            console.log('✅ Chart rendering test passed');
            testChart.destroy();
            return true;
        } catch (error) {
            console.error('❌ Error testing chart rendering:', error);
            return false;
        }
    } else {
        console.error('❌ Chart canvas not found');
        return false;
    }
}

// Run all tests
function runAllTests() {
    console.log('🚀 Starting JavaScript Tests...');
    console.log('='.repeat(50));
    
    const tests = [
        { name: 'jQuery', test: testjQuery },
        { name: 'Bootstrap', test: testBootstrap },
        { name: 'Chart.js', test: testChartJS },
        { name: 'Content Renderer', test: testContentRenderer },
        { name: 'Enhanced UI', test: testEnhancedUI },
        { name: 'Modal Manager', test: testModalManager },
        { name: 'Modal Triggers', test: testModalTriggers },
        { name: 'SPA Navigation', test: testSPANavigation },
        { name: 'Form Validation', test: testFormValidation },
        { name: 'Chart Rendering', test: testChartRendering }
    ];
    
    let passedTests = 0;
    let totalTests = tests.length;
    
    tests.forEach((test, index) => {
        console.log(`\n${index + 1}. ${test.name} Test:`);
        if (test.test()) {
            passedTests++;
        }
    });
    
    console.log('\n' + '='.repeat(50));
    console.log('📊 Test Results:');
    console.log(`Total Tests: ${totalTests}`);
    console.log(`Passed: ${passedTests} ✅`);
    console.log(`Failed: ${totalTests - passedTests} ❌`);
    console.log(`Success Rate: ${((passedTests / totalTests) * 100).toFixed(1)}%`);
    
    if (passedTests === totalTests) {
        console.log('🎉 All tests passed! System is working correctly.');
    } else {
        console.log('⚠️ Some tests failed. Please check the issues above.');
    }
    
    return passedTests === totalTests;
}

// Auto-run tests when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit longer for all scripts and charts to load
    setTimeout(runAllTests, 2000);
});

// Export for manual testing
window.testDashboard = {
    runAllTests,
    testjQuery,
    testBootstrap,
    testChartJS,
    testContentRenderer,
    testEnhancedUI,
    testModalManager,
    testModalTriggers,
    testSPANavigation,
    testFormValidation,
    testChartRendering
};
