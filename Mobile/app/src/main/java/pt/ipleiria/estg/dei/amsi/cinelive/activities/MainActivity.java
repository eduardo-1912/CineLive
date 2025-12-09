package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;

import androidx.appcompat.app.AppCompatActivity;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;
import androidx.navigation.ui.AppBarConfiguration;
import androidx.navigation.ui.NavigationUI;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityMainBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.StandardListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.AuthManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.ComprasManager;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorUtils;

public class MainActivity extends AppCompatActivity {

    private ActivityMainBinding binding;
    private NavHostFragment navHostFragment;
    private NavController navController;
    private AuthManager authManager;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        binding = ActivityMainBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        // Obter o NavController
        navHostFragment = (NavHostFragment)getSupportFragmentManager().findFragmentById(R.id.nav_host_fragment);
        navController = navHostFragment.getNavController();

        // Configurar
        AppBarConfiguration appBarConfiguration = new AppBarConfiguration.Builder
            (R.id.navFilmes, R.id.navCinemas, R.id.navCompras, R.id.navPerfil, R.id.navEntrar).build();

        // Toolbar integrada com o Navigation Component
        setSupportActionBar(binding.toolbar.topAppBar);
        NavigationUI.setupActionBarWithNavController(this, navController, appBarConfiguration);

        // BottomNavigation ligado ao NavController
        NavigationUI.setupWithNavController(binding.bottomNav, navController);

        // Obter o auth manager
        authManager = AuthManager.getInstance();

        // Iniciar base de dados local
        ComprasManager.getInstance().init(getApplicationContext());
    }

    private void load() {
        setOnNavItemSelectedListener();

        // Verificar ligação à internet
        if (!ConnectionUtils.hasInternet(this)) {
            updateBottomNav(authManager.isLoggedIn(this));
            return;
        }

        if (!authManager.isLoggedIn(this)) {
            updateBottomNav(false);
            return;
        }

        // Validar o token se tiver sessão iniciada
        if (authManager.isLoggedIn(this)) authManager.validateToken(this, new StandardListener() {
            @Override
            public void onSuccess() {
                updateBottomNav(true);
            }

            @Override
            public void onError() {
                updateBottomNav(false);
            }
        });
    }

    private void setOnNavItemSelectedListener() {
        binding.bottomNav.setOnItemSelectedListener(item -> {
            // Configurações
            if (item.getItemId() == R.id.navConfiguracoes && !authManager.isLoggedIn(this)) {
                // Verificar ligação à internet
                if (!ConnectionUtils.hasInternet(this)) {
                    ErrorUtils.showToast(this, ErrorUtils.Type.NO_INTERNET);
                    return false;
                }

                startActivity(new Intent(this, ConfiguracoesActivity.class));
                return false;
            }

            // Login
            if (item.getItemId() == R.id.navEntrar && !authManager.isLoggedIn(this)) {
                // Verificar ligação à internet
                if (!ConnectionUtils.hasInternet(this)) {
                    ErrorUtils.showToast(this, ErrorUtils.Type.NO_INTERNET);
                    return false;
                }

                startActivity(new Intent(this, LoginActivity.class));
                return false;
            }

            // Comportamento normal da navigation
            NavigationUI.onNavDestinationSelected(item, navController);
            return true;
        });
    }

    private void updateBottomNav(boolean isLoggedIn) {
        binding.bottomNav.getMenu().findItem(R.id.navConfiguracoes).setVisible(!isLoggedIn);
        binding.bottomNav.getMenu().findItem(R.id.navEntrar).setVisible(!isLoggedIn);
        binding.bottomNav.getMenu().findItem(R.id.navCompras).setVisible(isLoggedIn);
        binding.bottomNav.getMenu().findItem(R.id.navPerfil).setVisible(isLoggedIn);
    }

    public void navigateToFragment(int fragment) {
        binding.bottomNav.setSelectedItemId(fragment);
    }

    @Override
    protected void onResume() {
        super.onResume();
        load();
    }
}