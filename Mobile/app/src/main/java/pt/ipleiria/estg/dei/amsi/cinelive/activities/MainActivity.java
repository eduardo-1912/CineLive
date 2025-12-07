package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;
import androidx.navigation.ui.AppBarConfiguration;
import androidx.navigation.ui.NavigationUI;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityMainBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ConnectionListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.ValidateTokenListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.AuthManager;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;

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
    }

    private void load() {
        // Validar o token (se o tiver nas preferences)
        if (authManager.isLoggedIn(this)) authManager.validateToken(this, new ValidateTokenListener() {
            @Override
            public void onSuccess() {
                updateBottomNav(true);
            }

            @Override
            public void onError() {
                updateBottomNav(false);
            }
        });
        else updateBottomNav(false);

        // Ação do botão de login
        binding.bottomNav.setOnItemSelectedListener(item -> {
            if (item.getItemId() == R.id.navEntrar && !authManager.isLoggedIn(this)) {
                // Verificar se tem internet
                if (!ConnectionUtils.hasInternet(this)) {
                    Toast.makeText(this, R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
                }
                else startActivity(new Intent(this, LoginActivity.class));
                return false;
            }

            // Comportamento normal da navigation
            NavigationUI.onNavDestinationSelected(item, navController);
            return true;
        });
    }

    @Override
    protected void onResume() {
        super.onResume();
        load();
    }

    private void updateBottomNav(boolean isLoggedIn) {
        binding.bottomNav.getMenu().findItem(R.id.navCompras).setVisible(isLoggedIn);
        binding.bottomNav.getMenu().findItem(R.id.navPerfil).setVisible(isLoggedIn);
        binding.bottomNav.getMenu().findItem(R.id.navEntrar).setVisible(!isLoggedIn);
    }

    public void navigateToFragment(int fragment) {
        binding.bottomNav.setSelectedItemId(fragment);
    }
}